<?php

function getServiceArchetypePaths() {
  $dirs = [];
  foreach (glob('archetypes/service-*.yml') as $archetype) {
    if ($archetype == '.' || $archetype == '..') continue;
    $machine = str_replace("archetypes/service-", "", $archetype);
    $machine = str_replace(".yml", "", $machine);
    $dirs[$machine] = $archetype;
  }
  return $dirs;
}

function getServiceArchetypeStrings(array $dirs) {
  $vars = [];
  foreach ($dirs as $machine => $dir) {
    $vars[$machine] = stripComments(file_get_contents($dirs[$machine]));
  }
  return $vars;
}

function getServiceIniPaths() {
  $dirs = [];
  foreach (glob('config/machines/*.ini') as $ini) {
    if ($ini == '.' || $ini == '..') continue;
    $machine = str_replace("archetypes/service-", "", $ini);
    $machine = str_replace(".yml", "", $machine);
    $dirs[$machine] = $ini;
  }
  return $dirs;
}

function stripComments(string $template) {
  $pattern = '/(?<indent>^[ \t]*)?\{#[^}]*#\}(?<trailing>[ \t]*\r?\n)?/m';

  return preg_replace_callback($pattern, function ($matches) {
    $hasIndent = !empty($matches['indent']);
    $hasTrailingNewline = !empty($matches['trailing']);
    if ($hasIndent && $hasTrailingNewline) {
      return '';
    }
    return ($matches['indent'] ?? '') . ($matches['trailing'] ?? '');
  }, $template);
}

enum KnownServiceTypes: String {
  case ComputerType = "computerType";
  case RouterType   = "routerType";
  case ServerType   = "serverType";
}

function serviceBlockAppender(
  array  $networks,
  array  $services,
  array  $settingsData,
  string $serviceJoinNetworkString,
  string $outputFile
) {

  foreach ($services as $serviceType => $serviceData) {

    $knownType = KnownServiceTypes::tryFrom($serviceType);
    //error_log($knownType->value);

    if (isset($knownType)) {

      $port = 8080;

      /*
       * Einar2 provides by default 3 archetypes,
       * each of them defining a service type
       *
       * This triggers the classic logic of listing all saved
       * archetypes on the docker image and choosing one
       *
       */

      $serviceTypeName = str_replace("Type", "", $serviceType);
      $archetypeString = stripComments(file_get_contents("archetypes/service-" . $serviceTypeName . ".yml"));

      foreach ($serviceData as $serviceTypeItem) {
        serviceBlockKnownTypeAppender(
          $networks,
          $settingsData,
          $serviceType,
          $serviceTypeItem,
          $port++,
          $archetypeString,
          $serviceJoinNetworkString,
          $outputFile
        );
      }
    }
  }
}

function serviceBlockKnownTypeAppender(
  array  $networks,
  array  $settingsData,
  string $deviceType,
  array  $device,
  int    $port,
  string $serviceBlock,
  string $serviceJoinNetwork,
  string $outputFile
) {

  $myServiceBlock = $serviceBlock;

  if ($deviceType == "serverType") {
    $myServiceBlock = str_replace('{{ Port }}', $port, $myServiceBlock);
  }

  $myServiceBlock = str_replace('{{ Name }}', $device['name'], $myServiceBlock);
  $myServiceBlock = str_replace('{{ Image }}', $settingsData["einar2"]["image"], $myServiceBlock);

  file_put_contents($outputFile, $myServiceBlock, FILE_APPEND);

  $ifCount     = $device['if_number'];
  if ($ifCount != 0) {
    for ($i = 0; $i < $ifCount; $i++) {

      $myServiceJoinNetwork = $serviceJoinNetwork;

      $switch             = "switch" . $device['if_list'][$i]['if'];
      $dashedNetwork      = str_replace('.', '-', $networks[$switch]['network']);
      $address            = $device['if_list'][$i]['ip'];

      $myServiceJoinNetwork = str_replace('{{ NetworkDashed }}', $dashedNetwork, $myServiceJoinNetwork);
      $myServiceJoinNetwork = str_replace('{{ Address }}', $address, $myServiceJoinNetwork);

      file_put_contents($outputFile, $myServiceJoinNetwork, FILE_APPEND);
    }
  }
}

function serviceBlockCustomTypeAppender(
  array  $services,
  array  $networks,
  array  $iniDirMachines,
  array  $settingsData,
  string $serviceJoinNetwork,
  string $outputFile
) {

  $serviceBlock = stripComments(file_get_contents("archetypes/service-custom.yml"));

  $customServices = [];
  foreach ($services as $serviceItem) {
    if ($serviceItem != "computerType" && $serviceItem != "serverType" && $serviceItem != "routerType") {
      $customServices[] = $serviceItem;
    }
  }

  $configPath = "config/machines/" . $deviceName . ".ini";
  $config     = parse_ini_file($configPath, true);

  $archetypeArrayPlaceholders = [
    '{{ CapArray }}'  => ['Capabilities', 'CapArray'],
    '{{ EnvArray }}'  => ['Environments', 'EnvArray'],
    '{{ SysArray }}'  => ['Sysctls', 'SysArray'],
    '{{ VolArray }}'  => ['Volumes', 'VolumeArray'],
    '{{ PortArray }}' => ['Ports', 'PortArray']
  ];

  $archetypeValues = [];
  foreach ($archetypeArrayPlaceholders as $arrayPlaceholder => [$sectionName, $arrayKey]) {

    $items = $config[$sectionName][$arrayKey] ?? [];

    if (!empty($items)) {
      $archetypeValues[$arrayPlaceholder] = json_encode($items);
    } else {
      $archetypeValues[$arrayPlaceholder] = '[]';
    }
  }

  foreach ($customService as $deviceType => $device) {

    $myServiceBlock = $serviceBlock;

    /* Override */
    $myServiceBlock = str_replace('{{ Command }}', $config['Docker']['command'], $myServiceBlock);
    $myServiceBlock = str_replace('{{ HostName }}', $config['Docker']['hostname'], $myServiceBlock);
    if ($config['Docker']['image'] != "default") { // e.g. roarenas/einar2:latest
      $myServiceBlock = str_replace('{{ Image }}', $config['Docker']['image'], $myServiceBlock);
    }

    /* General */
    $myServiceBlock = str_replace('{{ Name }}', $device['name'], $myServiceBlock);
    $myServiceBlock = str_replace('{{ DefaultGateway }}', $device['gw'], $myServiceBlock);
    if ($config['Docker']['image'] == "default") {
      $myServiceBlock = str_replace('{{ Image }}', $settingsData["einar2"]["image"], $myServiceBlock);
    }

    /* Custom */
    foreach ($archetypeValues as $valuePlaceholder => $value) {
      if ($value === '[]') {
        // remove entire line if empty
        $pattern = '/^.*' . preg_quote(trim($valuePlaceholder), '/') . '.*\r?\n?/m';
        $myServiceBlock = preg_replace($pattern, '', $myServiceBlock);
      } else {
        // replace placeholder with array string
        $myServiceBlock = str_replace($valuePlaceholder, $value, $myServiceBlock);
      }
    }

    file_put_contents($outputFile, $myServiceBlock, FILE_APPEND);

    $ifCount     = $device['if_number'];
    if ($ifCount != 0) {
      for ($i = 0; $i < $ifCount; $i++) {

        $myServiceJoinNetwork = $serviceJoinNetwork;

        $switch             = "switch" . $device['if_list'][$i]['if'];
        $dashedNetwork      = str_replace('.', '-', $networks[$switch]['network']);
        $address            = $device['if_list'][$i]['ip'];

        $myServiceJoinNetwork = str_replace('{{ NetworkDashed }}', $dashedNetwork, $myServiceJoinNetwork);
        $myServiceJoinNetwork = str_replace('{{ Address }}', $address, $myServiceJoinNetwork);

        file_put_contents($outputFile, $myServiceJoinNetwork, FILE_APPEND);
      }
    }
  }
}

function networkBlockAppender(
  array  $switches,
  string $networkBlock,
  string $outputFile
) {

  foreach ($switches as $switch) {

    $myNetworkBlock = $networkBlock;

    $dashedNetwork = str_replace('.', '-', $switch['network']);

    $myNetworkBlock = str_replace('{{ NetworkDashed }}', $dashedNetwork, $myNetworkBlock);
    $myNetworkBlock = str_replace('{{ Network }}', $switch['network'], $myNetworkBlock);
    $myNetworkBlock = str_replace('{{ Mask }}', $switch['mask'], $myNetworkBlock);
    $myNetworkBlock = str_replace('{{ HostBindGateway }}', $switch['gateway'], $myNetworkBlock);

    file_put_contents($outputFile, $myNetworkBlock, FILE_APPEND);
  }
}

?>

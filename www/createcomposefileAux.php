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
    $serviceTypeName = str_replace("Type", "", $serviceType);

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

      $archetypeString = stripComments(file_get_contents(__DIR__ . "/archetypes/service-" . $serviceTypeName . ".yml"));

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
    } else {

      $metaArchetypeString = stripComments(file_get_contents(__DIR__ . "/archetypes/service-custom.yml"));
      $serviceTypeIniPath  = __DIR__ . '/config/machines/' . $serviceTypeName . '.ini';

      if (!file_exists($serviceTypeIniPath)) continue; /* Generate custom archetype on-the-fly or quit */
      $archetypeString = generateArchetypeFromIni($metaArchetypeString,
                                                  $settingsData,
                                                  $serviceTypeIniPath,
                                                  $serviceTypeName);

      foreach ($serviceData as $serviceTypeItem) {
        serviceBlockKnownTypeAppender(
          $networks,
          $settingsData,
          $serviceType,
          $serviceTypeItem,
          -1, // {{ Port }} won't exist here
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

function generateArchetypeFromIni(
  string $metaArchetype,
  array  $settingsData,
  string $iniPath,
  string $typeVar
) {

  $config = parse_ini_file($iniPath, true);
  $archetype = $metaArchetype;

  $docker         = $config['Docker'] ?? [];
  $dockerImage    = $docker['image'] ?? "default";
  $dockerCommand  = $docker['command'] ?? "sleep inf";
  $dockerHostname = $docker['hostname'] ?? "{{ Type }}-{{ Name }}";


  if ($docker['image'] == "default") {
    $docker['image'] = $settingsData["einar2"]["image"];
  }
  $archetype = str_replace('{{ Image }}', $docker['image'], $archetype);
  $archetype = str_replace('{{ Command }}', $docker['command'], $archetype);
  $archetype = str_replace('{{ HostName }}', $docker['hostname'], $archetype);

  $nonEmptyArrayOrStripLine = function(string $placeholder, array $items, string &$text) {
    if (empty($items)) {
      $pattern = '/^[^\r\n]*' . preg_quote($placeholder, '/') . '[^\r\n]*(\r\n|\n)?/m';
      $text = preg_replace($pattern, '', $text);
    } else {
      $text = str_replace($placeholder, json_encode($items, JSON_UNESCAPED_SLASHES), $text);
    }
  };

  $capabilities = $config['Capabilities'] ?? [];
  $capArray = $capabilities['CapArray'] ?? [];
  $nonEmptyArrayOrStripLine('{{ CapArray }}', $capArray, $archetype);

  $environments = $config['Environments'] ?? [];
  $envArray = $environments['EnvArray'] ?? [];
  $nonEmptyArrayOrStripLine('{{ EnvArray }}', $envArray, $archetype);

  $sysctls = $config['Sysctls'] ?? [];
  $sysArray = $sysctls['SysArray'] ?? [];
  $nonEmptyArrayOrStripLine('{{ SysArray }}', $sysArray, $archetype);

  $volumes = $config['Volumes'] ?? [];
  $volArray = $volumes['VolArray'] ?? [];
  $nonEmptyArrayOrStripLine('{{ VolArray }}', $volArray, $archetype);

  $ports = $config['Ports'] ?? [];
  $portArray = $ports['PortArray'] ?? [];
  $nonEmptyArrayOrStripLine('{{ PortArray }}', $portArray, $archetype);

  /* Some arrays have this variable inside */
  $archetype = str_replace('{{ Type }}', $typeVar, $archetype);

  return $archetype;
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

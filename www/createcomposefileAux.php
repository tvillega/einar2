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
    $serviceTypeName = str_replace("Type", "", $serviceType);
    $knownType = KnownServiceTypes::tryFrom($serviceType);

    if (isset($knownType)) {
      $filePath = __DIR__ . "/archetypes/service-" . $serviceTypeName . ".yml";
      if (!file_exists($filePath)) continue;
      $archetypeString = stripComments(file_get_contents($filePath));
    } else {
      $iniPath = __DIR__ . '/config/machines/' . $serviceTypeName . '.ini';
      if (!file_exists($iniPath)) continue;

      $metaArchetype = stripComments(file_get_contents(__DIR__ . "/archetypes/service-custom.yml"));
      $archetypeString = generateArchetypeFromIni($metaArchetype, $settingsData, $iniPath, $serviceTypeName);
    }

    $port = 8080;
    foreach ($serviceData as $serviceTypeItem) {
      $currentPort = ($serviceType === "serverType") ? $port++ : -1;

      appendService(
        $networks,
        $settingsData,
        $serviceType,
        $serviceTypeItem,
        $currentPort,
        $archetypeString,
        $serviceJoinNetworkString,
        $outputFile
      );
    }
  }
}

function appendService(
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
  $myServiceBlock = str_replace('{{ DefaultGateway }}', $device['gw'], $myServiceBlock);

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

  /* Get all sections or safely set as empty array */
  $docker         = $config['Docker']       ?? [];
  $environments   = $config['Environments'] ?? [];
  $capabilities   = $config['Capabilities'] ?? [];
  $sysctls        = $config['Sysctls']      ?? [];
  $volumes        = $config['Volumes']      ?? [];
  $ports          = $config['Ports']        ?? [];

  /* Get arrays of each section or safely set as empty array */
  $capArray  = $capabilities['CapArray'] ?? [];
  $envArray  = $environments['EnvArray'] ?? [];
  $sysArray  = $sysctls['SysArray']      ?? [];
  $volArray  = $volumes['VolArray']      ?? [];
  $portArray = $ports['PortArray']       ?? [];

  /* [Docker] Get variables or safely set as !fail string */
  $dockerImage      = $docker['image']      ?? "!fail";
  $dockerEntrypoint = $docker['entrypoint'] ?? "!fail";
  $dockerCommand    = $docker['command']    ?? "!fail";
  $dockerUser       = $docker['user']       ?? "!fail";

  /* [Docker] Patch mandatory variable */
  if ($dockerImage == "!fail") {
    $dockerImage = $settingsData['einar2']['image'] ?? "!fail";
  }

  /* Allow a single key=value if for whatever reason someone forgets it */
  $ensureArray = function(&$arr) {
    if (!is_array($arr)) {
      $arr = ($arr !== '') ? [$arr] : [];
    }
  };

  /* We need to do this here before patching their mandatory values */
  $ensureArray($capArray);
  $ensureArray($envArray);
  $ensureArray($sysArray);
  $ensureArray($volArray);
  $ensureArray($portArray);

  /* [Environments] Patch mandatory variable */
  array_unshift($envArray, "DEFAULT_GATEWAY={{ DefaultGateway }}");

  /* [Capabilities] Patch mandatory variable */
  array_unshift($capArray, "NET_ADMIN");

  /* [Sysctls] Patch mandatory variables */
  array_unshift($sysArray, "net.ipv4.conf.default.rp_filter=0");
  array_unshift($sysArray, "net.ipv4.conf.all.rp_filter=0");

  $nonFailStringOrStripLine = function(string $placeholder, string $data, string &$text) {
    if ($data == "!fail") {
      $pattern = '/^[^\r\n]*' . preg_quote($placeholder, '/') . '[^\r\n]*(\r\n|\n)?/m';
      $text = preg_replace($pattern, '', $text);
    } else {
      $text = str_replace($placeholder, $data, $text);
    }
  };

  $nonEmptyArrayOrStripLine = function(string $placeholder, array $items, string &$text) {
    if (empty($items)) {
      $pattern = '/^[^\r\n]*' . preg_quote($placeholder, '/') . '[^\r\n]*(\r\n|\n)?/m';
      $text = preg_replace($pattern, '', $text);
    } else {
      $text = str_replace($placeholder, json_encode($items, JSON_UNESCAPED_SLASHES), $text);
    }
  };

  /* Replace all placeholders with their respective strings */
  $nonFailStringOrStripLine('{{ Image }}', $dockerImage, $archetype);
  $nonFailStringOrStripLine('{{ Entrypoint }}', $dockerEntrypoint, $archetype);
  $nonFailStringOrStripLine('{{ Command }}', $dockerCommand, $archetype);
  $nonFailStringOrStripLine('{{ User }}', $dockerUser, $archetype);

  /* Replace all placeholders with their respective arrays */
  $nonEmptyArrayOrStripLine('{{ CapArray }}', $capArray, $archetype);
  $nonEmptyArrayOrStripLine('{{ EnvArray }}', $envArray, $archetype);
  $nonEmptyArrayOrStripLine('{{ SysArray }}', $sysArray, $archetype);
  $nonEmptyArrayOrStripLine('{{ VolArray }}', $volArray, $archetype);
  $nonEmptyArrayOrStripLine('{{ PortArray }}', $portArray, $archetype);

  /* Should happen regardless of user preferences */
  $archetype = str_replace('{{ HostName }}', '{{ Type }}-{{ Name }}', $archetype);
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

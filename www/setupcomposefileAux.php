<?php

function getServiceArchetypeDirs() {
  $dirs = [];
  foreach (glob('archetypes/service-*.yml') as $archetype) {
    if ($archetype == '.' || $archetype == '..') continue;
    $machine = str_replace("archetypes/service-", "", $archetype);
    $machine = str_replace(".yml", "", $machine);
    $dirs[$machine] = $archetype;
  }
  return $dirs;
}

function getServiceArchetypeVars(array $dirs) {
  $vars = [];
  foreach ($dirs as $machine => $dir) {
    $vars[$machine] = stripComments(file_get_contents($dirs[$machine]));
  }
  return $vars;
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

function serviceBlockTypeAppender(
  array  $networks,
  array  $settingsData,
  string $deviceType,
  array  $devices,
  string $serviceBlock,
  string $serviceJoinNetwork,
  string $outputFile
) {

  $port = 8080;

  foreach ($devices as $device) {

    $myServiceBlock = $serviceBlock;

    $myServiceBlock = str_replace('{{ Name }}', $device['name'], $myServiceBlock);

    if ($deviceType == "server") {
      $myServiceBlock = str_replace('{{ Port }}', $port, $myServiceBlock);
      $port++;
    }

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
    $myNetworkBlock = str_replace('{{ Gateway }}', $switch['gateway'], $myNetworkBlock);

    file_put_contents($outputFile, $myNetworkBlock, FILE_APPEND);
  }
}

?>

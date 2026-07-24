<?php

$queryStringSet    = false;

$labName           = $_GET['laboratory'];
$labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
$labPath           = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName;
$labDataFile       = $labPath . "/lab.json";

if (file_exists($labDataFile)) {
  $jsonRaw = file_get_contents($labDataFile);
  $labData = json_decode($jsonRaw, true);
} else {
  die("Error: Laboratory has not been created or initialized.");
}

$routersNumber   = $labData['routers'];
$serversNumber   = $labData['servers'];
$computersNumber = $labData['computers'];
$switchesNumber  = $labData['switches'];

$networksPath    = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName . '/networks.json';
$servicesPath    = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName . '/services.json';

if (file_exists($networksPath)) {
  $networks = json_decode(file_get_contents($networksPath), true);
} else {
  die("Error: Laboratory networks have not been created or initialized.");
}

if (file_exists($servicesPath)) {
  $services = json_decode(file_get_contents($servicesPath), true);
} else {
  die("Error: Laboratory services have not been created or initialized.");
}

$formSubmitted       = false;
$queryStringSet      = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  echo '<pre>';
  print_r($_POST);
  echo '</pre>';

  /* All output files */
  $outputComposeServicesFile = $labPath . '/docker-compose.yml';
  $outputComposeNetworksFile = $labPath . '/docker-compose-networks.yml';
  $outputJsonFile            = $labPath . '/docker-compose.json';

  $archetypesDir = __DIR__  . '/archetypes';

  /* All archetype directories */
  $archetypeDirComposeNetworks  = $archetypesDir . '/compose-networks.yml';
  $archetypeDirComposeServices  = $archetypesDir . '/compose-services.yml';
  $archetypeDirJoinNetwork      = $archetypesDir . '/join-network.yml';
  $archetypeDirServiceComputer  = $archetypesDir . '/service-computer.yml';
  $archetypeDirServiceRouter    = $archetypesDir . '/service-router.yml';
  $archetypeDirServiceServer    = $archetypesDir . '/service-server.yml';

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

  /* All archetypes contents without comments */
  $archetypeVarComposeNetworks  = stripComments(file_get_contents($archetypeDirComposeNetworks));
  $archetypeVarComposeServices  = stripComments(file_get_contents($archetypeDirComposeServices));
  $archetypeVarJoinNetwork      = stripComments(file_get_contents($archetypeDirJoinNetwork));
  $archetypeVarServiceComputer  = stripComments(file_get_contents($archetypeDirServiceComputer));
  $archetypeVarServiceRouter    = stripComments(file_get_contents($archetypeDirServiceRouter));
  $archetypeVarServiceServer    = stripComments(file_get_contents($archetypeDirServiceServer));

  file_put_contents($outputComposeServicesFile, $archetypeVarComposeServices);
  file_put_contents($outputComposeNetworksFile, $archetypeVarComposeNetworks);

  function serviceBlockTypeAppender(
    string $deviceType,
    array  $devices,
    string $serviceBlock,
    string $serviceJoinNetwork,
    string $outputFile
  ) {

    foreach ($devices as $device) {
      // $serviceBlock = str_replace('{{NAME}}', $deviceName, $content);
      file_put_contents($outputFile, $serviceBlock, FILE_APPEND);
      $ifCount     = $device['if_number'];
      if ($ifCount != 0) {
          for ($i = 0; $i < $ifCount; $i++) {
            // $netBlock = str_replace('{{IF}}', $deviceIf, $content);
            file_put_contents($outputFile, $serviceJoinNetwork, FILE_APPEND);
          }
      }
    }
  }

  if (!empty($services['routers'])) {
    serviceBlockTypeAppender(
      'router',
      $services['routers'],
      $archetypeVarServiceRouter,
      $archetypeVarJoinNetwork,
      $outputComposeServicesFile
    );
  }

  if (!empty($services['servers'])) {
    serviceBlockTypeAppender(
      'server',
      $services['servers'],
      $archetypeVarServiceServer,
      $archetypeVarJoinNetwork,
      $outputComposeServicesFile
    );
  }

  if (!empty($services['computers'])) {
    serviceBlockTypeAppender(
      'computer',
      $services['computers'],
      $archetypeVarServiceComputer,
      $archetypeVarJoinNetwork,
      $outputComposeServicesFile
    );
  }



  // file_put_contents($labPath . '/docker-compose.json', json_encode($compose, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['laboratory']) && isset($_GET['submitted'])) {


  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $outputComposeServicesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/docker-compose.yml';
  $outputComposeNetworksFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/docker-compose-networks.yml';

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup interfaces</title>
    <style>
        body {
            padding: 20px;
        }
        .form-group {
            padding: 5px 0px;
        }
        .form-group label {
            padding-right: 10px;
        }
        .submit-container {
            padding-top: 15px;
        }
        .output-container {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

    <!-- Send POST with form data to myself (executes the code at the top)  -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?laboratory=" . $_GET['laboratory']; ?>" method="post">

    <h1>Docker Compose</h1>

    <h2>Generate docker compose file</h2>
        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
        </div>

    <div>
        <pre>
<!-- <pre> captures the indentation -->
<?php
    echo file_get_contents($outputComposeServicesFile);
    echo file_get_contents($outputComposeNetworksFile);
?>
        </pre>
    </div>


</body>
</html>

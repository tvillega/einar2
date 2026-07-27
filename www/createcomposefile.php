<?php

// DEVEL MODE
$develMode = false;
$develFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . '.develmode';
$develData = [];
if (file_exists($develFile)) {
  $develMode    = true;
  $develJsonRaw = file_get_contents($develFile);
  $develData    = json_decode($develJsonRaw, true);
  echo "<pre>";
  echo "DEVEL MODE => ";
  print_r($develData);
  echo "</pre>";
}

$queryStringSet    = false;
$formSubmitted     = false;

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

  /* All output files */
  $outputComposeServicesFile = $labPath . '/docker-compose.yml';
  $outputComposeNetworksFile = $labPath . '/docker-compose-networks.yml';
  $outputJsonFile            = $labPath . '/docker-compose.json';

  $archetypesDir = __DIR__  . '/archetypes';

  /* All archetype directories */
  $archetypeDirComposeNetworks  = $archetypesDir . '/compose-networks.yml';
  $archetypeDirComposeServices  = $archetypesDir . '/compose-services.yml';
  $archetypeDirJoinNetwork      = $archetypesDir . '/join-network.yml';
  $archetypeDirNetworkBridge    = $archetypesDir . '/network-bridge.yml';
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
  $archetypeVarNetworkBridge    = stripComments(file_get_contents($archetypeDirNetworkBridge));
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

    $port = 8080;

    foreach ($devices as $device) {

      global $networks;
      global $develMode;
      global $develData;

      $myServiceBlock = $serviceBlock;

      $myServiceBlock = str_replace('{{ Name }}', $device['name'], $myServiceBlock);

      if ($deviceType == "server") {
        $myServiceBlock = str_replace('{{ Port }}', $port, $myServiceBlock);
        $port++;
      }

      // DEVEL FLAG
      if ($develMode) {
        $customImage    = $develData["einar2"]["image"];
        $myServiceBlock = str_replace('tvillega/einar2:latest', $customImage, $myServiceBlock);
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

  networkBlockAppender(
    $networks,
    $archetypeVarNetworkBridge,
    $outputComposeNetworksFile
  );


  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['laboratory']) && isset($_GET['submitted'])) {


  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $outputComposeServicesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/docker-compose.yml';
  $outputComposeNetworksFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/docker-compose-networks.yml';
  $formSubmitted = true;

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

        <div style="margin-top: 15px;">
            <a href="/setupservices.php<?php echo "?laboratory=" . $_GET['laboratory']; ?>">Previous (Edit Services)</a>
            <a href="/index.php"> | Home</a>
        </div>

    <?php if ($formSubmitted): ?>
    <div>
    <hr>
    <p>Files have been saved on directory <?php echo $labPath; ?></p>
    <div style="width: 100%; display: table;">
        <div style="display: table-row">
            <div style="width: 500px; display: table-cell;">
                <h4>docker-compose.yml</h4>
<pre>
<?php echo file_get_contents($outputComposeServicesFile); ?>
</pre>
            </div>
            <div style="display: table-cell;">
                <h4>docker-compose-networks.yml</h4>
<pre>
<?php echo file_get_contents($outputComposeNetworksFile); ?>
</pre>
            </div>
        </div>
    </div>
    <?php endif; ?>


</body>
</html>

<?php

require __DIR__ . '/setupcomposefileAux.php';

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/settings.json';
if (!file_exists($settingsFile)) {
  die("File settings.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

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

$machineArchetypeDirs = [];
$machineArchetypeVars = [];

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

  /* All archetype dirs */
  $archetypeDirComposeNetworks  = $archetypesDir . '/compose-networks.yml';
  $archetypeDirComposeServices  = $archetypesDir . '/compose-services.yml';
  $archetypeDirJoinNetwork      = $archetypesDir . '/join-network.yml';
  $archetypeDirNetworkBridge    = $archetypesDir . '/network-bridge.yml';
  $archetypeDirMachines         = getServiceArchetypeDirs();

  /* All archetypes contents without comments */
  $archetypeVarComposeNetworks  = stripComments(file_get_contents($archetypeDirComposeNetworks));
  $archetypeVarComposeServices  = stripComments(file_get_contents($archetypeDirComposeServices));
  $archetypeVarJoinNetwork      = stripComments(file_get_contents($archetypeDirJoinNetwork));
  $archetypeVarNetworkBridge    = stripComments(file_get_contents($archetypeDirNetworkBridge));
  $archetypeVarMachines         = getServiceArchetypeVars($archetypeDirMachines);

  file_put_contents($outputComposeServicesFile, $archetypeVarComposeServices);
  file_put_contents($outputComposeNetworksFile, $archetypeVarComposeNetworks);

  foreach ($archetypeDirMachines as $machineName => $machineDir) {
    $machineType = $machineName . "Type";
    serviceBlockTypeAppender(
      $networks,
      $settingsData,
      $machineName,
      $services[$machineType],
      $archetypeVarMachines[$machineName],
      $archetypeVarJoinNetwork,
      $outputComposeServicesFile
    );
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

<?php

$labName           = $_GET['laboratory'];
$labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
$labPath           = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName;
$labDataFile       = $labPath . "/lab.json";

$jsonRaw = file_get_contents($labDataFile);
$labData = json_decode($jsonRaw, true);

$switchesNumber = $labData['switches'];

$labNetworksExists = false;
$queryStringSet    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $labName           = $_GET['laboratory'] ?? '';
  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
  $labDir            = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized;

  $jsonPath          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/lab.json';

  if (file_exists($jsonPath)) {
    $labDataExists  = true;
    $labData        = json_decode(file_get_contents($jsonPath), true);
    $queryStringSet = true;
    $queryString    = '?laboratory=' . urlencode($labNameNormalized);
  } else {
    die("Error: Laboratory has not been created or initialized.");
  }

  $switchesNumber = $labData['switches'];
  $networks = [];

  for ($i = 1; $i <= $switchesNumber; $i++) {

    $formKey = "switch" . $i;
    if (isset($_POST[$formKey])) {
      $prefix  = trim($_POST[$formKey]['prefix']  ?? '');
      $mask    = trim($_POST[$formKey]['mask']    ?? '');
      $gateway = trim($_POST[$formKey]['gateway'] ?? '');

      $networks[$formKey] = [
        "prefix"  => $prefix,
        "mask"    => $mask,
        "gateway" => $gateway
      ];
    }
  }

  file_put_contents($labDir . '/networks.json', json_encode($networks, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['laboratory']) && isset($_GET['submitted'])) {

  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $jsonPath          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/networks.json';

  if (file_exists($jsonPath)) {
    $labNetworksExists  = true;
    $labNetworks        = json_decode(file_get_contents($jsonPath), true);
    $queryStringSet     = true;
    $queryString        = '?laboratory=' . urlencode($labNameNormalized);
  }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup networks</title>
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

        <?php if ($queryStringSet): ?>
            <h1>Editing <?php echo $labData['lab_name']; ?> networks</h1>
        <?php else: ?>
            <h1>Setup <?php echo $labData['lab_name']; ?> networks</h1>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $switchesNumber; $i++): ?>
            <fieldset>
                <legend>Switch<?php echo $i; ?></legend>
                
                <div class="form-group">
                    <label for="prefix_<?php echo $i; ?>">Prefix:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labNetworksExists && isset($labNetworks['switch' . $i]['prefix']))
                                          ? htmlspecialchars($labNetworks['switch' . $i]['prefix'])
                                          : '';
                                  ?>"
                           id="prefix_<?php echo $i; ?>"
                           name="switch<?php echo $i; ?>[prefix]"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="mask_<?php echo $i; ?>">Mask:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labNetworksExists && isset($labNetworks['switch' . $i]['mask']))
                                          ? htmlspecialchars($labNetworks['switch' . $i]['mask'])
                                          : '';
                                  ?>"
                           id="mask_<?php echo $i; ?>"
                           name="switch<?php echo $i; ?>[mask]"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="gateway_<?php echo $i; ?>">Gateway:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labNetworksExists && isset($labNetworks['switch' . $i]['gateway']))
                                          ? htmlspecialchars($labNetworks['switch' . $i]['gateway'])
                                          : '';
                                  ?>"
                           id="gateway_<?php echo $i; ?>"
                           name="switch<?php echo $i; ?>[gateway]"
                           required>
                </div>
            </fieldset>
        <?php endfor; ?>

        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
        </div>
    </form>

    <?php if ($labNetworksExists): ?>

        <h2>Networks loaded</h2>
        <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

        <?php for ($i = 1; $i <= sizeof($labNetworks); $i++): ?>
            <hr>
            <div id="results">

                <?php
                    $key = "switch" . $i;
                    $validForm = true;
                ?>

                <p>Switch <?php echo $i; ?></p>

                <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Prefix:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labNetworks[$key]['prefix']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if (filter_var($labNetworks[$key]['prefix'], FILTER_VALIDATE_IP)) {
                                    echo '<strong style="color:green";>OK</strong>';
                                } else {
                                    echo '<strong style="color:red;">Invalid</strong>';
                                    $validForm = false;
                                }
                            ?>
                        </div>
                    </div>

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Mask:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labNetworks[$key]['mask']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if ($labNetworks[$key]['mask'] < 0 || $labNetworks[$key]['mask'] > 32) {
                                    echo '<strong style="color:red;">Invalid</strong>';
                                    $validForm = false;
                                } else {
                                    echo '<strong style="color:green;">OK</strong>';
                                }
                            ?>
                        </div>
                    </div>


                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Gateway:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labNetworks[$key]['gateway']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if (filter_var($labNetworks[$key]['gateway'], FILTER_VALIDATE_IP)) {
                                    echo '<strong style="color:green;">OK</strong>';
                                } else {
                                    echo '<strong style="color:red;">Invalid</strong>';
                                    $validForm = false;
                                }
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        <?php endfor; ?>
    <?php endif; ?>

    <div style="margin-top: 15px;">
        <a href="/createlab.php<?php echo "?laboratory=" . $_GET['laboratory']; ?>">Previous (Edit Lab)</a>
            |
        <a href="/index.php">Home</a>
        <?php if ($queryStringSet && $validForm): ?>
            | <a href="/setupservices.php<?php echo $queryString; ?>">Next (Setup Services)</a>
        <?php elseif (!$validForm): ?>
            | <span style="color: gray; cursor: not-allowed;" title="One or more fields are not valid">Next (Setup Services)</span>
        <?php else: ?>
            | <span style="color: gray; cursor: not-allowed;" title="Submit networks fist">Next (Setup Services)</span>
        <?php endif; ?>
    </div>

</body>
</html>

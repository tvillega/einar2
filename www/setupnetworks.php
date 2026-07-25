<?php

require __DIR__ . '/ip_in_range.php';

/* Global variables */
$validForm         = true;
$labNetworksExists = false;
$queryStringSet    = false;
$queryString       = null;

/* Laboratory configurations */
$labName           = $_GET['laboratory'];
$labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
$labPath           = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized;
$labFile           = $labPath . "/lab.json";
$jsonRaw           = file_get_contents($labFile);
$labData           = json_decode($jsonRaw, true);

$switchesNumber = $labData['switches']-1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $queryStringSet = true;
  $queryString    = '?laboratory=' . urlencode($labNameNormalized);

  $networks = [];

  for ($i = 0; $i <= $switchesNumber; $i++) {

    $switch = "switch" . $i;
    if (isset($_POST[$switch])) {
      $network = trim($_POST[$switch]['network'] ?? '');
      $mask    = trim($_POST[$switch]['mask']    ?? '');
      $gateway = trim($_POST[$switch]['gateway'] ?? '');

      $networks[$switch] = [
        "network" => $network,
        "mask"    => $mask,
        "gateway" => $gateway
      ];
    }
  }

  file_put_contents($labPath . '/networks.json', json_encode($networks, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['submitted'])) {

  $jsonFile          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/networks.json';
  $jsonRaw           = file_get_contents($jsonFile, true);
  $networksData      = json_decode($jsonRaw, true);

  $queryStringSet    = true;
  $queryString       = '?laboratory=' . urlencode($labNameNormalized);
  $labNetworksExists = true;

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

        <?php
            $labDataName = $labData['lab_name'];
        ?>

        <?php if ($queryStringSet): ?>
            <h1>Editing <?php echo $labDataName ?> networks</h1>
        <?php else: ?>
            <h1>Setup <?php echo $labDataName; ?> networks</h1>
        <?php endif; ?>

        <h2>Step 2 of 4</h2>

        <?php for ($i = 0; $i <= $switchesNumber; $i++): ?>
            <fieldset style="background-color: #F8F8FF;">

                <?php
                    $switch                = "switch" . $i;

                    $netTagLabelAttrFor    = "network" . $i;
                    $netTagInputAttrValue  = ($labNetworksExists && isset($networksData[$switch]['network']))
                                                ? htmlspecialchars($networksData[$switch]['network'])
                                                : '';
                    $netTagInputAttrName   = $switch . "[network]";

                    $maskTagLabelAttrFor   = "mask" . $i;
                    $maskTagInputAttrValue = ($labNetworksExists && isset($networksData[$switch]['mask']))
                                                ? htmlspecialchars($networksData[$switch]['mask'])
                                                : '';
                    $maskTagInputAttrName  = $switch . "[mask]";

                    $gwTagLabelAttrFor     = "gateway" . $i;
                    $gwTagInputAttrValue   = ($labNetworksExists && isset($networksData[$switch]['gateway']))
                                                ? htmlspecialchars($networksData[$switch]['gateway'])
                                                : '';
                    $gwTagInputAttrName    = $switch . "[gateway]";
                ?>

                <legend><?php echo $switch; ?></legend>

                <div class="form-group">
                    <label for="<?php echo $netTagLabelAttrFor; ?>">Network:</label>
                    <input type="text"
                           value="<?php echo $netTagInputAttrValue; ?>"
                           id="<?php echo $netTagLabelAttrFor; ?>"
                           name="<?php echo $netTagInputAttrName; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="<?php echo $maskTagLabelAttrFor; ?>">Mask:</label>
                    <input type="text"
                           value="<?php echo $maskTagInputAttrValue; ?>"
                           id="<?php echo $maskTagLabelAttrFor; ?>"
                           name="<?php echo $maskTagInputAttrName; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="<?php echo $gwTagLabelAttrFor; ?>">Gateway:</label>
                    <input type="text"
                           value="<?php echo $gwTagInputAttrValue; ?>"
                           id="<?php echo $gwTagLabelAttrFor; ?>"
                           name="<?php echo $gwTagInputAttrName; ?>"
                           required>
                </div>

            </fieldset>
        <?php endfor; ?>

        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
            <?php
                $networksFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/networks.json';
                if (file_exists($networksFile)) {
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?laboratory=" . $_GET['laboratory'] . "&submitted" . ">(load saved configuration)" . "</a>";
                }
            ?>
        </div>
    </form>

    <?php if ($labNetworksExists): ?>

        <h2>Networks loaded</h2>
        <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

        <?php for ($i = 0; $i <= $switchesNumber; $i++): ?>
            <hr>
            <div id="results">

                <?php
                    $switch      = "switch" . $i;
                    $networkNet  = $networksData[$switch]['network'];
                    $networkMask = $networksData[$switch]['mask'];
                    $networkGw   = $networksData[$switch]['gateway'];
                    $networkCidr = $networkNet . "/" . $networkMask;
                ?>

                <p>Switch <?php echo $i; ?></p>

                <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Network:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($networkNet); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if (filter_var($networkNet, FILTER_VALIDATE_IP)) {
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
                            <?php echo htmlspecialchars($networkMask); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if ($networkMask < 0 || $networkMask > 32) {
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
                            <?php echo htmlspecialchars($networkGw); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if (!filter_var($networkGw, FILTER_VALIDATE_IP)) {
                                    echo '<strong style="color:red;">Invalid</strong>';
                                    $validForm = false;
                                } else if (!ipv4_in_range($networkGw,$networkCidr)) {
                                    echo '<strong style="color:red;">Failed</strong>';
                                    $validForm = false;
                                } else {
                                    echo '<strong style="color:green;">OK</strong>';
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
            | <a href="/setupinterfaces.php<?php echo $queryString; ?>">Next (Setup Interfaces)</a>
        <?php elseif (!$validForm): ?>
            | <span style="color: gray; cursor: not-allowed;" title="One or more fields are not valid">Next (Setup Interfaces)</span>
        <?php else: ?>
            | <span style="color: gray; cursor: not-allowed;" title="Submit networks fist">Next (Setup Interfaces)</span>
        <?php endif; ?>
    </div>

</body>
</html>

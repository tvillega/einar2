<?php

/* Global variables */
$validForm         = true;
$formSubmitted = false;
$queryStringSet    = false;
$queryString       = null;

/* Laboratory configurations */
$labName           = $_GET['laboratory'];
$labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
$labPath           = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized;
$labFile           = $labPath . "/lab.json";
$jsonRaw           = file_get_contents($labFile);
$labData           = json_decode($jsonRaw, true);

$deviceCount             = [];
$deviceCount['router']   = $labData['routers'];
$deviceCount['server']   = $labData['servers'];
$deviceCount['computer'] = $labData['computers'];

$switchesNumber          = $labData['switches'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $queryStringSet = true;
  $queryString    = '?laboratory=' . urlencode($labNameNormalized);

  $services = [];

  /* Retrieve interfaces data */

  if (isset($_POST)) {
    $services = $_POST;
  }

  file_put_contents($labPath . '/services.json', json_encode($services, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['submitted'])) {

  $jsonFile          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
  $jsonRaw           = file_get_contents($jsonFile, true);
  $servicesData      = json_decode($jsonRaw, true);

  $queryStringSet    = true;
  $queryString       = '?laboratory=' . urlencode($labNameNormalized);
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

    <h1><?php echo (($queryStringSet) ? "Editing " : "Setup ") . $labData['lab_name'] . " interfaces"; ?></h1>
    <h2>Step 3 of 4</h2>

    <?php
        foreach (['router', 'server', 'computer'] as $device):

          $deviceNumber = isset($deviceCount[$device]) ? $deviceCount[$device] : 0;

          if ($deviceNumber != 0):

            $deviceType  = $device . "s";
            $deviceTitle = ucfirst($deviceType);
    ?>

        <h3><?php echo $deviceTitle; ?></h3>

            <?php for ($i = 0; $i <= $deviceNumber-1; $i++): ?>
                <fieldset style="background-color: #F8F8FF;">

                    <?php
                        $deviceID                     = $device . $i;

                        $nameTagLabelAttrFor          = $device . "_name";
                        $nameTagInputAttrValue        = ($formSubmitted && isset($servicesData[$deviceType][$deviceID]['name']))
                                                            ? htmlspecialchars($servicesData[$deviceType][$deviceID]['name'])
                                                            : '';
                        $nameTagInputAttrName         = $deviceType . "[" . $deviceID . "][name]";

                        $ifnumberTagLabelAttrFor      = $deviceID . "_ifnumber";
                        $ifnumberSavedValue           = ($formSubmitted && isset($servicesData[$deviceType][$deviceID]['if_number']))
                                                            ? $servicesData[$deviceType][$deviceID]['if_number']
                                                            : null;
                        $ifnumberTagInputAttrName     = $deviceType . "[" . $deviceID . "][if_number]";

                    ?>


                    <legend><?php echo $device . $i; ?></legend>

                    <div class="form-group">
                        <label for="<?php echo $nameTagLabelAttrFor; ?>">Name:</label>
                        <input type="text"
                              value="<?php echo $nameTagInputAttrValue; ?>"
                              id="<?php echo $nameTagLabelAttrFor; ?>"
                              name="<?php echo $nameTagInputAttrName; ?>"
                              required>
                    </div>

                    <div class="form-group">
                        <label for="<?php echo $ifnumberTagLabelAttrFor; ?>">Interfaces:</label>
                        <select id="<?php echo $ifnumberTagLabelAttrFor; ?>"
                                name="<?php echo $ifnumberTagInputAttrName; ?>"
                                required>

                        <?php for ($j = 1; $j <= $switchesNumber; $j++): ?>

                            <?php $isSelected = ($ifnumberSavedValue !== null && $ifnumberSavedValue == $j) ? 'selected' : ''; ?>

                            <option value="<?php echo $j; ?>" <?php echo $isSelected; ?>>
                                <?php echo $j; ?>
                            </option>

                        <?php endfor; ?>

                        </select>
                    </div>
                </fieldset>
            <?php endfor; ?>

        <?php endif; ?>
    <?php endforeach; ?>

        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
            <?php
                $servicesFileFound = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
                if (file_exists($servicesFileFound)) {
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?laboratory=" . $_GET['laboratory'] . "&submitted" . ">(load saved configurations)" . "</a>";
                }
            ?>
        </div>
        <?php
            $servicesFileFound = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
            if (file_exists($servicesFileFound) && $formSubmitted) {
                echo '<div style="padding-top: 10px;">';
                echo '    <strong style="color:chocolate;">Warning: Submitting new configurations will undo changes made on the services editor (step 4).</strong>';
                echo '</div>';
        }
        ?>
        <div style="padding-top: 10px;">
            <?php>
        </div>
    </form>

    <?php if ($formSubmitted): ?>

        <h2>Interfaces loaded</h2>
        <p>To update its values, fill the form and submit it again.</p>

    <?php endif; ?>

    <div style="margin-top: 15px;">
        <a href="/setupnetworks.php<?php echo "?laboratory=" . $_GET['laboratory'] . "&submitted"; ?>">Previous (Edit Networks)</a>
        |
        <a href="/index.php">Home</a>
        <?php if ($queryStringSet && $validForm): ?>
            | <a href="/setupservices.php<?php echo $queryString; ?>">Next (Setup Services)</a>
        <?php elseif (!$validForm): ?>
            | <span style="color: gray; cursor: not-allowed;" title="One or more fields are not valid">Next (Setup Services)</span>
        <?php else: ?>
            | <span style="color: gray; cursor: not-allowed;" title="Submit services fist">Next (Setup Services)</span>
        <?php endif; ?>
    </div>

</body>
</html>

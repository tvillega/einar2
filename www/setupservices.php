<?php

require __DIR__ . '/ip_in_range.php';

/* Global variables */
$validForm         = true;
$formSubmitted     = false;
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

$networksPath    = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName . '/networks.json';
$servicesPath    = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName . '/services.json';

$networks = json_decode(file_get_contents($networksPath), true);
$services = json_decode(file_get_contents($servicesPath), true);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  foreach (['router', 'server', 'computer'] as $device) {
    $deviceType = $device . "s";
    if ($deviceCount[$device] != 0 && isset($_POST[$deviceType])) {
      $services[$deviceType] = array_replace_recursive($services[$deviceType], $_POST[$deviceType]);
    }
  }

  file_put_contents($labPath . '/services.json', json_encode($services, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['laboratory']) && isset($_GET['submitted'])) {


  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $jsonPath          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';

  if (file_exists($jsonPath)) {
    $formSubmitted     = true;
    $queryStringSet    = true;
    $queryString       = '?laboratory=' . urlencode($labNameNormalized);
  }

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

    <h1><?php echo (($queryStringSet) ? "Editing " : "Setup ") . $labData['lab_name'] . " services"; ?></h1>
    <h2>Step 4 of 4</h2>

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
                        $deviceName                   = $services[$deviceType][$deviceID]['name'];
                        $deviceIfnumber               = $services[$deviceType][$deviceID]['if_number']-1;
                    ?>

                    <legend><?php echo $deviceName; ?></legend>

                    <?php for ($j = 0; $j <= $deviceIfnumber; $j++): ?>

                        <?php $eth = "eth" . $j; ?>

                        <?php if ($j != 0): ?>
                            <hr style="border: 1.3px dashed; margin: 0.1em;">
                        <?php endif; ?>

                        <div class="form-group">
                            <?php echo "<strong>" . $eth . "</strong>"; ?>
                        </div>


                        <div style="display: table; width: 30%;">

                            <div style="display: table-row;">
                                <div style="display: table-cell; padding: 5px; vertical-align: middle; width: 30%;">
                                    <?php
                                        $ifconnTagLabelAttrFor    = $deviceID . "_if" . $j;
                                        $ifconnTagSelectAttrName  = $deviceType . "[" . $deviceID . "][if_list][" . $j . "][if]";
                                        $ifconnTagSelectValue     = ($formSubmitted && isset($services[$deviceType][$deviceID]['if_list'][$j]['if']))
                                                                    ? $services[$deviceType][$deviceID]['if_list'][$j]['if']
                                                                    : null;
                                    ?>
                                    <label for="<?php echo $ifconnTagLabelAttrFor; ?>">Connected to:</label>
                                </div>
                                <div style="display: table-cell; padding: 5px; vertical-align: middle; width: 70%;">
                                    <select id="<?php echo $ifconnTagLabelAttrFor; ?>"
                                            name="<?php echo $ifconnTagSelectAttrName; ?>"
                                            required>

                                        <?php for ($k = 0; $k <= $switchesNumber-1; $k++): ?>

                                            <?php
                                                $isSelected = ($formSubmitted && $ifconnTagSelectValue !== null && $ifconnTagSelectValue == $k) ? 'selected' : '';
                                                $switch     = 'switch' . $k;
                                                $cidr       = $networks[$switch]['network'] . "/" . $networks[$switch]['mask'];
                                            ?>

                                            <option value="<?php echo $k; ?>" <?php echo $isSelected; ?>>
                                                <?php echo $cidr ?>
                                            </option>

                                        <?php endfor; ?>

                                    </select>
                                </div>
                            </div>

                            <div style="display: table-row;">
                                <div style="display: table-cell; padding: 5px; vertical-align: middle; width: 30%;">
                                    <?php
                                        $ipTagLabelAttrFor   = $deviceID . "_ip" . $j;
                                        $ipTagInputAttrName  = $deviceType . "[" . $deviceID . "][if_list][" . $j . "][ip]";
                                        $ipTagInputAttrValue = ($formSubmitted && isset($services[$deviceType][$deviceID]['if_list'][$j]["ip"]))
                                                                    ? htmlspecialchars($services[$deviceType][$deviceID]['if_list'][$j]["ip"])
                                                                    : '';
                                    ?>
                                    <label for="<?php echo $ipTagLabelAttrFor; ?>">ip:</label>
                                </div>
                                <div style="display: table-cell; padding: 5px; vertical-align: middle; width: 70%;">
                                    <input type="text"
                                          value="<?php echo $ipTagInputAttrValue ?>"
                                          id="<?php echo $ipTagLabelAttrFor; ?>"
                                          name="<?php echo $ipTagInputAttrName; ?>"
                                          required>
                                </div>
                            </div>

                        </div>

                    <?php endfor; ?>
                </fieldset>
            <?php endfor; ?>
        <?php endif; ?>
    <?php endforeach; ?>

        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
            <?php
                $servicesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
                if (file_exists($servicesFile)) {
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?laboratory=" . $_GET['laboratory'] . "&submitted" . ">(load saved configurations)" . "</a>";
                }
            ?>
        </div>
    </form>

    <?php if ($formSubmitted): ?>

        <h2>Services loaded</h2>
        <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

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
                <legend><?php echo $deviceTitle; ?></legend>

                    <?php
                        $deviceID                     = $device . $i;
                        $deviceName                   = $services[$deviceType][$deviceID]['name'];
                        $deviceIfnumber               = $services[$deviceType][$deviceID]['if_number']-1;
                        $deviceIflist                 = $services[$deviceType][$deviceID]['if_list'];
                    ?>

                    <p>
                        <style>
                            .device-wrap:hover .device-id,
                            .device-wrap:hover .port-id {
                                opacity: 1 !important;
                                visibility: visible !important;
                            }
                        </style>

                        <span class="device-wrap">
                            <?php echo "Name: <i>" . $deviceName . "</i>"; ?>
                            <span class="device-id" style="color: #4169E1; opacity: 0; visibility: hidden; transition: opacity 0.3s ease;">
                                (<?php echo $deviceID; ?>)
                            </span>

                            <?php if ($device == 'server'): ?>
                                <?php
                                    $portNumber = 8080 + $i;
                                    $serverAddress    = "http://127.0.0.1:" . $portNumber;
                                ?>
                                <span class="port-id" style="color: #4169E1; opacity: 0; visibility: hidden; transition: opacity 0.3s ease; margin-left: 2px;">
                                    -> <a target="_blank"
                                       href="<?php echo $serverAddress; ?>"
                                       style="color: #4169E1;">
                                          <i><?php echo $serverAddress; ?></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </span>
                    </p>

                    <div style="display: table; width: 70%;">

                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 5px; font-weight: bold; width: 10%">
                                Interface
                            </div>
                            <div style="display: table-cell; padding: 5px; font-weight: bold; width: 10%">
                                CIDR
                            </div>
                            <div style="display: table-cell; padding: 5px; font-weight: bold; width: 10%">
                                IP
                            </div>
                            <div style="display: table-cell; padding: 5px; font-weight: bold; width: 10%">
                                GW
                            </div>
                            <div style="display: table-cell; padding: 5px; font-weight: bold; width: 10%">
                                Check
                            </div>

                        </div>

                        <?php for ($j = 0; $j <= $deviceIfnumber; $j++): ?>

                            <?php
                                $ip     = $deviceIflist[$j]['ip'];
                                $if     = $deviceIflist[$j]['if'];
                                $switch = 'switch' . $if;
                                $net    = $networks[$switch]['network'];
                                $mask   = $networks[$switch]['mask'];
                                $gw     = $networks[$switch]['gateway'];
                                $cidr   = $net . "/" . $mask;
                                $check  = ipv4_in_range($ip, $cidr);
                            ?>

                            <div style="display: table-row;">
                                <div style="display: table-cell; padding: 5px; font-style:italic; width: 10%">
                                    <?php echo htmlspecialchars("eth" . $j); ?>
                                </div>
                                <div style="display: table-cell; padding: 5px; width: 10%">
                                    <?php echo htmlspecialchars($cidr); ?>
                                </div>
                                <div style="display: table-cell; padding: 5px; width: 10%">
                                    <?php echo htmlspecialchars($ip); ?>
                                </div>
                                <div style="display: table-cell; padding: 5px; width: 10%">
                                    <?php echo htmlspecialchars($gw) ?>
                                </div>
                                <div style="display: table-cell; padding: 5px; width: 10%">
                                    <?php
                                        if (!$check) {
                                            echo '<strong style="color:red;">FAILED</strong>';
                                            $validForm = false;
                                        } else {
                                            echo '<strong style="color:green">OK</strong>';
                                        }
                                    ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    </fieldset>
                <?php endfor; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top: 15px;">
        <a href="/setupinterfaces.php<?php echo "?laboratory=" . $_GET['laboratory']; ?>">Previous (Edit Interfaces)</a>
        |
        <a href="/index.php">Home</a>
        <?php if ($queryStringSet && $validForm): ?>
            | <a href="/createcomposefile.php<?php echo $queryString; ?>">Next (Create Compose File)</a>
        <?php elseif (!$validForm): ?>
            | <span style="color: gray; cursor: not-allowed;" title="One or more fields are not valid">Next (Create Compose File)</span>
        <?php else: ?>
            | <span style="color: gray; cursor: not-allowed;" title="Submit services fist">Next (Create Compose File)</span>
        <?php endif; ?>
    </div>

</body>
</html>

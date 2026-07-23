<?php

require __DIR__ . '/ip_in_range.php';

$validForm         = true;
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

  /* Update routers data */

  $routersNumber   = $labData['routers'];

  if ($routersNumber != 0) {

    for ($i = 1; $i <= $routersNumber; $i++) {

      $key = "router" . $i;
      $if_number = $services['routers'][$key]['if_number'];

      if (($if_number != 0) && (isset($_POST[$key]['if_list']))) {
          $services["routers"][$key]['if_list'] = $_POST[$key]['if_list'];
      }
    }
  }

  /* Update servers data */

  $serversNumber   = $labData['servers'];

  if ($serversNumber != 0) {

    for ($i = 1; $i <= $serversNumber; $i++) {

      $key = "server" . $i;
      $if_number = $services['servers'][$key]['if_number'];

      if (($if_number != 0) && (isset($_POST[$key]['if_list']))) {
        $services["servers"][$key]['if_list'] = $_POST[$key]['if_list'];
      }
    }
  }

  /* Update computers data */

  $computersNumber   = $labData['computers'];

  if ($computersNumber != 0) {

    for ($i = 1; $i <= $computersNumber; $i++) {

      $key = "computer" . $i;
      $if_number = $services['computers'][$key]['if_number'];

      if (($if_number != 0) && (isset($_POST[$key]['if_list']))) {
        $services["computers"][$key]['if_list'] = $_POST[$key]['if_list'];
      }
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

    <?php if ($queryStringSet): ?>
        <h1>Editing <?php echo $labData['lab_name']; ?> services</h1>
    <?php else: ?>
        <h1>Setup <?php echo $labData['lab_name']; ?> services</h1>
    <?php endif; ?>

    <h2>Step 4 of 4</h2>

    <!-- Form for routers -->

    <?php if ($routersNumber != 0): ?>

        <h3>Routers</h3>

        <?php for ($i = 1; $i <= $routersNumber; $i++): ?>
            <fieldset>
                <legend>Router <?php echo $i; ?></legend>

                 <?php
                    $router                = 'router' . $i;
                    $routerInterfaceNumber = $services['routers'][$router]['if_number']-1;
                 ?>

                 <?php for ($j = 0; $j <= $routerInterfaceNumber; $j++): ?>

                    <?php
                        $eth = "eth" . $j;
                    ?>

                    <hr style="border: 1.3px dashed; margin: 0.1em;">
                    <div class="form-group">
                        <?php echo "<strong>" . $eth . "</strong>"; ?>
                    </div>

                    <!-- Selector for interfaces  -->

                    <div class="form-group">
                        <?php
                            $ifconnTagLabelAttrFor    = $router . "_if_" . $j;
                            $ifconnTagSelectAttrName  = $router . "[if_list][" . $j . "][if]";
                            $ifconnTagSelectValue     = (isset($services['routers'][$router]['if_list'][$j]['if']))
                                                          ? $services['routers'][$router]['if_list'][$j]['if']
                                                          : null;
                        ?>
                        <label for="<?php echo $ifconnTagLabelAttrFor; ?>">Connected to:</label>
                        <select id="<?php echo $ifconnTagLabelAttrFor; ?>"
                                name="<?php echo $ifconnTagSelectAttrName; ?>"
                                required>

                        <?php for ($k = 0; $k <= $routerInterfaceNumber; $k++): ?>

                            <?php
                                $isSelected = ($ifconnTagSelectValue !== null && $ifconnTagSelectValue == $k) ? 'selected' : '';
                                $switch     = 'switch' . ($k+1);
                                $cidr       = $networks[$switch]['prefix'] . "/" . $networks[$switch]['mask'];
                            ?>

                            <option value="<?php echo $k; ?>" <?php echo $isSelected; ?>>
                                <?php echo $cidr ?>
                            </option>

                        <?php endfor; ?>

                        </select>
                    </div>

                    <!-- Input for ip -->

                    <div class="form-group">
                        <?php
                            $ipTagLabelAttrFor   = $router . "_ip_" . $j;
                            $ipTagInputAttrName  = $router . "[if_list][" . $j . "][ip]";
                            $ipTagInputAttrValue = (isset($services['routers'][$router]['if_list'][$j]["ip"]))
                                                        ? htmlspecialchars($services['routers'][$router]['if_list'][$j]["ip"])
                                                        : '';
                        ?>
                        <label for="<?php echo $ipTagLabelAttrFor; ?>">ip:</label>
                        <input type="text"
                               value="<?php echo $ipTagInputAttrValue ?>"
                               id="<?php echo $ipTagLabelAttrFor; ?>"
                               name="<?php echo $ipTagInputAttrName; ?>"
                               required>
                    </div>

                 <?php endfor; ?>

            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>

    <!-- Form for servers -->

    <?php if ($serversNumber != 0): ?>

        <h3>Servers</h3>

        <?php for ($i = 1; $i <= $serversNumber; $i++): ?>
            <fieldset>
                <legend>Server <?php echo $i; ?></legend>

                <?php
                  $server                = 'server' . $i;
                  $serverInterfaceNumber = $services['servers'][$server]['if_number']-1;
                ?>

                <?php for ($j = 0; $j <= $serverInterfaceNumber; $j++): ?>

                    <?php
                        $eth = "eth" . $j;
                    ?>

                    <hr style="border: 1.3px dashed; margin: 0.1em;">
                    <div class="form-group">
                        <?php echo "<strong>" . $eth . "</strong>"; ?>
                    </div>

                    <!-- Selector for interfaces  -->

                    <div class="form-group">
                        <?php
                            $ifconnTagLabelAttrFor    = $server . "_if_" . $j;
                            $ifconnTagSelectAttrName  = $server . "[if_list][" . $j . "][if]";
                            $ifconnTagSelectValue     = (isset($services['servers'][$server]['if_list'][$j]['if']))
                                                          ? $services['servers'][$server]['if_list'][$j]['if']
                                                          : null;
                        ?>
                        <label for="<?php echo $ifconnTagLabelAttrFor; ?>">Connected to:</label>
                        <select id="<?php echo $ifconnTagLabelAttrFor; ?>"
                                name="<?php echo $ifconnTagSelectAttrName; ?>"
                                required>

                        <?php for ($k = 0; $k <= $serverInterfaceNumber; $k++): ?>

                            <?php
                                $isSelected = ($ifconnTagSelectValue !== null && $ifconnTagSelectValue == $k) ? 'selected' : '';
                                $switch     = 'switch' . ($k+1);
                                $cidr       = $networks[$switch]['prefix'] . "/" . $networks[$switch]['mask'];
                            ?>

                            <option value="<?php echo $k; ?>" <?php echo $isSelected; ?>>
                                <?php echo $cidr ?>
                            </option>

                        <?php endfor; ?>

                        </select>
                    </div>

                    <!-- Input for ip -->

                    <div class="form-group">
                        <?php
                            $ipTagLabelAttrFor   = $server . "_ip_" . $j;
                            $ipTagInputAttrName  = $server . "[if_list][" . $j . "][ip]";
                            $ipTagInputAttrValue = (isset($services['servers'][$server]['if_list'][$j]["ip"]))
                                                        ? htmlspecialchars($services['servers'][$server]['if_list'][$j]["ip"])
                                                        : '';
                        ?>
                        <label for="<?php echo $ipTagLabelAttrFor; ?>">ip:</label>
                        <input type="text"
                              value="<?php echo $ipTagInputAttrValue ?>"
                              id="<?php echo $ipTagLabelAttrFor; ?>"
                              name="<?php echo $ipTagInputAttrName; ?>"
                              required>
                    </div>

                <?php endfor; ?>

            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>

    <!-- Form for computers -->

    <?php if ($computersNumber != 0): ?>

        <h3>Computers</h3>

        <?php for ($i = 1; $i <= $computersNumber; $i++): ?>
            <fieldset>
                <legend>Computer <?php echo $i; ?></legend>

                <?php
                  $computer                = 'computer' . $i;
                  $computerInterfaceNumber = $services['computers'][$computer]['if_number']-1;
                ?>

                <?php for ($j = 0; $j <= $computerInterfaceNumber; $j++): ?>

                    <?php
                        $eth = "eth" . $j;
                    ?>

                    <hr style="border: 1.3px dashed; margin: 0.1em;">
                    <div class="form-group">
                        <?php echo "<strong>" . $eth . "</strong>"; ?>
                    </div>

                    <!-- Selector for interfaces  -->

                    <div class="form-group">
                        <?php
                            $ifconnTagLabelAttrFor    = $computer . "_if_" . $j;
                            $ifconnTagSelectAttrName  = $computer . "[if_list][" . $j . "][if]";
                            $ifconnTagSelectValue     = (isset($services['computers'][$computer]['if_list'][$j]['if']))
                                                          ? $services['computers'][$computer]['if_list'][$j]['if']
                                                          : null;
                        ?>
                        <label for="<?php echo $ifconnTagLabelAttrFor; ?>">Connected to:</label>
                        <select id="<?php echo $ifconnTagLabelAttrFor; ?>"
                                name="<?php echo $ifconnTagSelectAttrName; ?>"
                                required>

                        <?php for ($k = 0; $k <= $computerInterfaceNumber; $k++): ?>

                            <?php
                                $isSelected = ($ifconnTagSelectValue !== null && $ifconnTagSelectValue == $k) ? 'selected' : '';
                                $switch     = 'switch' . ($k+1);
                                $cidr       = $networks[$switch]['prefix'] . "/" . $networks[$switch]['mask'];
                            ?>

                            <option value="<?php echo $k; ?>" <?php echo $isSelected; ?>>
                                <?php echo $cidr ?>
                            </option>

                        <?php endfor; ?>

                        </select>
                    </div>

                    <!-- Input for ip -->

                    <div class="form-group">
                        <?php
                            $ipTagLabelAttrFor   = $computer . "_ip_" . $j;
                            $ipTagInputAttrName  = $computer . "[if_list][" . $j . "][ip]";
                            $ipTagInputAttrValue = (isset($services['computers'][$computer]['if_list'][$j]["ip"]))
                                                        ? htmlspecialchars($services['computers'][$computer]['if_list'][$j]["ip"])
                                                        : '';
                        ?>
                        <label for="<?php echo $ipTagLabelAttrFor; ?>">ip:</label>
                        <input type="text"
                              value="<?php echo $ipTagInputAttrValue ?>"
                              id="<?php echo $ipTagLabelAttrFor; ?>"
                              name="<?php echo $ipTagInputAttrName; ?>"
                              required>
                    </div>

                <?php endfor; ?>

            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>

        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
            <?php
                $servicesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
                if (file_exists($servicesFile)) {
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?laboratory=" . $_GET['laboratory'] . "&submitted" . ">(load from file)" . "</a>";
                }
            ?>
        </div>
    </form>

    <?php if ($formSubmitted): ?>

        <h2>Services loaded</h2>
        <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

        <?php for ($i = 1; $i <= sizeof($services['routers']); $i++): ?>

            <hr>
            <div id="results_routers">

            <?php
                $router                = 'router' . $i;
                $routerInterfaceNumber = $services['routers'][$router]['if_number']-1;
                $routerInterfaces      = $services['routers'][$router]['if_list'];
            ?>

                <p>Router <?php echo $i; ?></p>

                <div style="display: table; width: 40%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Interface
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            CIDR
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            IP
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Check
                        </div>
                    </div>

                    <?php for ($j = 0; $j <= $routerInterfaceNumber; $j++): ?>

                        <?php
                            $ip     = $routerInterfaces[$j]['ip'];
                            $if     = $routerInterfaces[$j]['if'];
                            $switch = 'switch' . ($if+1);
                            $prefix = $networks[$switch]['prefix'];
                            $mask   = $networks[$switch]['mask'];
                            $cidr   = $prefix . "/" . $mask;
                            $check  = ipv4_in_range($ip, $cidr);
                        ?>

                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 5px; font-style:italic; width: 20%">
                                <?php echo htmlspecialchars("eth" . $j); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($cidr); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($ip); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php
                                    if ($check) {
                                        echo '<strong style="color:green">OK</strong>';
                                    } else {
                                        echo '<strong style="color:red;">FAILED</strong>';
                                        $validForm = false;
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
        <?php endfor; ?>

        <?php for ($i = 1; $i <= sizeof($services['servers']); $i++): ?>

            <hr>
            <div id="results_servers">

            <?php
                $server                = 'server' . $i;
                $serverInterfaceNumber = $services['servers'][$server]['if_number']-1;
                $serverInterfaces      = $services['servers'][$server]['if_list'];
            ?>

                <p>Server <?php echo $i; ?></p>

                <div style="display: table; width: 40%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Interface
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            CIDR
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            IP
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Check
                        </div>
                    </div>

                    <?php for ($j = 0; $j <= $serverInterfaceNumber; $j++): ?>

                        <?php
                            $ip     = $serverInterfaces[$j]['ip'];
                            $if     = $serverInterfaces[$j]['if'];
                            $switch = 'switch' . ($if+1);
                            $prefix = $networks[$switch]['prefix'];
                            $mask   = $networks[$switch]['mask'];
                            $cidr   = $prefix . "/" . $mask;
                            $check  = ipv4_in_range($ip, $cidr);
                        ?>

                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 5px; font-style:italic; width: 20%">
                                <?php echo htmlspecialchars("eth" . $j); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($cidr); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($ip); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php
                                    if ($check) {
                                        echo '<strong style="color:green">OK</strong>';
                                    } else {
                                        echo '<strong style="color:red;">FAILED</strong>';
                                        $validForm = false;
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
        <?php endfor; ?>

        <?php for ($i = 1; $i <= sizeof($services['computers']); $i++): ?>

            <hr>
            <div id="results_computers">

            <?php
                $comuter                 = 'computer' . $i;
                $computerInterfaceNumber = $services['computers'][$computer]['if_number']-1;
                $computerInterfaces      = $services['computers'][$computer]['if_list'];
            ?>

                <p>Computer <?php echo $i; ?></p>

                <div style="display: table; width: 40%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Interface
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            CIDR
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            IP
                        </div>
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 20%">
                            Check
                        </div>
                    </div>

                    <?php for ($j = 0; $j <= $computerInterfaceNumber; $j++): ?>

                        <?php
                            $ip     = $computerInterfaces[$j]['ip'];
                            $if     = $computerInterfaces[$j]['if'];
                            $switch = 'switch' . ($if+1);
                            $prefix = $networks[$switch]['prefix'];
                            $mask   = $networks[$switch]['mask'];
                            $cidr   = $prefix . "/" . $mask;
                            $check  = ipv4_in_range($ip, $cidr);
                        ?>

                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 5px; font-style:italic; width: 20%">
                                <?php echo htmlspecialchars("eth" . $j); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($cidr); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php echo htmlspecialchars($ip); ?>
                            </div>
                            <div style="display: table-cell; padding: 5px; width: 20%">
                                <?php
                                    if ($check) {
                                        echo '<strong style="color:green">OK</strong>';
                                    } else {
                                        echo '<strong style="color:red;">FAILED</strong>';
                                        $validForm = false;
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
        <?php endfor; ?>
    <?php endif; ?>

    <div style="margin-top: 15px;">
        <a href="/setupinterfaces.php<?php echo "?laboratory=" . $_GET['laboratory'] . "&submitted"; ?>">Previous (Edit Interfaces)</a>
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

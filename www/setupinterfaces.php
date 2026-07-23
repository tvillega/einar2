<?php

$validForm = true;

$labName           = $_GET['laboratory'];
$labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
$labPath           = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName;
$labDataFile       = $labPath . "/lab.json";

$jsonRaw = file_get_contents($labDataFile);
$labData = json_decode($jsonRaw, true);

$routersNumber   = $labData['routers'];
$serversNumber   = $labData['servers'];
$computersNumber = $labData['computers'];
$switchesNumber  = $labData['switches'];

$labServicesExists = false;
$queryStringSet    = false;

$networksPath    = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labName . '/networks.json';

if (file_exists($networksPath)) {
  $networks = json_decode(file_get_contents($networksPath), true);
} else {
  die("Error: Laboratory networks have not been created or initialized.");
}

$labServicesExists = false;
$queryStringSet      = false;

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

  $services = [];

  /* Retrieve routers data */

  $routersNumber   = $labData['routers'];
  $routers = [];

  if ($routersNumber != 0) {

    for ($i = 1; $i <= $routersNumber; $i++) {

      $formKey = "router" . $i;
      if (isset($_POST[$formKey])) {
        $name      = trim($_POST[$formKey]['name']      ?? '');
        $if_number = (int)trim($_POST[$formKey]['if_number'] ?? 0);

        $routers[$formKey] = [
          "name"       => $name,
          "if_number"  => $if_number,
          "if_list"    => []
        ];
      }
    }
    $services['routers'] = $routers;
  }

  /* Retrieve servers data */

  $serversNumber   = $labData['servers'];
  $servers = [];

  if ($serversNumber != 0) {

    for ($i = 1; $i <= $serversNumber; $i++) {

      $formKey = "server" . $i;
      if (isset($_POST[$formKey])) {
        $name      = trim($_POST[$formKey]['name']      ?? '');
        $if_number = (int)trim($_POST[$formKey]['if_number'] ?? 0);

        $servers[$formKey] = [
          "name"       => $name,
          "if_number"  => $if_number,
          "if_list"    => []
        ];
      }
    }
    $services['servers'] = $servers;
  }

  /* Retrieve computers data */

  $computersNumber = $labData['computers'];
  $computers = [];

  if ($computersNumber != 0) {

    for ($i = 1; $i <= $computersNumber; $i++) {

      $formKey = "computer" . $i;
      if (isset($_POST[$formKey])) {
        $name      = trim($_POST[$formKey]['name']      ?? '');
        $if_number = (int)trim($_POST[$formKey]['if_number'] ?? 0);

        $computers[$formKey] = [
          "name"       => $name,
          "if_number"  => $if_number,
          "if_list"    => []
        ];
      }
    }
    $services['computers'] = $computers;
  }

  file_put_contents($labDir . '/services.json', json_encode($services, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized) . "&submitted");

} else if (isset($_GET['laboratory']) && isset($_GET['submitted'])) {

  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $jsonPath          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';

  if (file_exists($jsonPath)) {
    $labServicesExists = true;
    $labServices       = json_decode(file_get_contents($jsonPath), true);
    $queryStringSet      = true;
    $queryString         = '?laboratory=' . urlencode($labNameNormalized);
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
        <h1>Editing <?php echo $labData['lab_name']; ?> interfaces</h1>
    <?php else: ?>
        <h1>Setup <?php echo $labData['lab_name']; ?> interfaces</h1>
    <?php endif; ?>

    <h2>Step 3 of 4</h2>

    <!-- Form for routers -->

    <?php if ($routersNumber != 0): ?>

        <h3>Routers</h3>

        <?php for ($i = 1; $i <= $routersNumber; $i++): ?>
            <fieldset>
                <legend>Router <?php echo $i; ?></legend>

                <div class="form-group">
                    <label for="router_name_<?php echo $i; ?>">Name:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labServicesExists && isset($labServices['routers']['router' . $i]['name']))
                                          ? htmlspecialchars($labServices['routers']['router' . $i]['name'])
                                          : '';
                                  ?>"
                           id="router_name_<?php echo $i; ?>"
                           name="router<?php echo $i; ?>[name]"
                           required>
                </div>

                <div class="form-group">
                    <label for="router_if_number_<?php echo $i; ?>">Interfaces:</label>
                    <select id="router_if_number_<?php echo $i; ?>"
                            name="router<?php echo $i; ?>[if_number]"
                            required>

                    <?php
                        $savedInterface = ($labServicesExists && isset($labServices['routers']['router' . $i]['if_number']))
                            ? $labServices['routers']['router' . $i]['if_number']
                            : null;
                    ?>

                    <?php for ($j = 0; $j <= $switchesNumber; $j++): ?>

                        <?php $isSelected = ($savedInterface !== null && $savedInterface == $j) ? 'selected' : ''; ?>

                        <option value="<?php echo $j; ?>" <?php echo $isSelected; ?>>
                            <?php echo $j; ?>
                        </option>

                    <?php endfor; ?>

                    </select>
                </div>
            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>

    <!-- Form for servers -->

    <?php if ($serversNumber != 0): ?>

        <h3>Servers</h3>

        <?php for ($i = 1; $i <= $serversNumber; $i++): ?>
            <fieldset>
                <legend>Server <?php echo $i; ?></legend>

                <div class="form-group">
                    <label for="server_name_<?php echo $i; ?>">Name:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labServicesExists && isset($labServices['servers']['server' . $i]['name']))
                                          ? htmlspecialchars($labServices['servers']['server' . $i]['name'])
                                          : '';
                                  ?>"
                           id="server_name_<?php echo $i; ?>"
                           name="server<?php echo $i; ?>[name]"
                           required>
                    </div>

                <div class="form-group">
                    <label for="server_if_number_<?php echo $i; ?>">Interfaces:</label>
                    <select id="server_if_number_<?php echo $i; ?>"
                            name="server<?php echo $i; ?>[if_number]"
                            required>

                    <?php
                        $savedInterface = ($labServicesExists && isset($labServices['servers']['server' . $i]['if_number']))
                            ? $labServices['servers']['server' . $i]['if_number']
                            : null;
                    ?>

                    <?php for ($j = 0; $j <= $switchesNumber; $j++): ?>

                        <?php $isSelected = ($savedInterface !== null && $savedInterface == $j) ? 'selected' : ''; ?>

                        <option value="<?php echo $j; ?>" <?php echo $isSelected; ?>>
                            <?php echo $j; ?>
                        </option>

                    <?php endfor; ?>

                    </select>
                </div>
            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>

    <!-- Form for computers -->

    <?php if ($computersNumber != 0): ?>

        <h3>Computers</h3>

        <?php for ($i = 1; $i <= $computersNumber; $i++): ?>
            <fieldset>
                <legend>Computer <?php echo $i; ?></legend>

                <div class="form-group">
                    <label for="computer_name_<?php echo $i; ?>">Name:</label>
                    <input type="text"
                           value="<?php
                                      echo ($labServicesExists && isset($labServices['computers']['computer' . $i]['name']))
                                          ? htmlspecialchars($labServices['computers']['computer' . $i]['name'])
                                          : '';
                                  ?>"
                           id="computer_name_<?php echo $i; ?>"
                           name="computer<?php echo $i; ?>[name]"
                           required>
                    </div>

                <div class="form-group">
                    <label for="computer_if_number_<?php echo $i; ?>">Interfaces:</label>
                    <select id="computer_if_number_<?php echo $i; ?>"
                            name="computer<?php echo $i; ?>[if_number]"
                            required>

                    <?php
                        $savedInterface = ($labServicesExists && isset($labServices['computers']['computer' . $i]['if_number']))
                            ? $labServices['computers']['computer' . $i]['if_number']
                            : null;
                    ?>

                    <?php for ($j = 0; $j <= $switchesNumber; $j++): ?>

                        <?php $isSelected = ($savedInterface !== null && $savedInterface == $j) ? 'selected' : ''; ?>

                        <option value="<?php echo $j; ?>" <?php echo $isSelected; ?>>
                            <?php echo $j; ?>
                        </option>

                    <?php endfor; ?>

                    </select>
                </div>
            </fieldset>
        <?php endfor; ?>

    <?php endif; ?>


        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
            <?php
                $interfacesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
                if (file_exists($interfacesFile)) {
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?laboratory=" . $_GET['laboratory'] . "&submitted" . ">(load from file)" . "</a>";
                }
            ?>
        </div>
        <?php
            $interfacesFile = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/services.json';
            if (file_exists($interfacesFile) && $labServicesExists) {
                echo '<div style="padding-top: 10px;">';
                echo '    <strong style="color:chocolate;">Warning: Submitting new configurations will undo changes made on the services editor (step 4).</strong>';
                echo '</div>';
        }
        ?>
        <div style="padding-top: 10px;">
            <?php>
        </div>
    </form>

    <?php if ($labServicesExists): ?>

        <h2>Interfaces loaded</h2>
        <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

        <?php for ($i = 1; $i <= sizeof($labServices['routers']); $i++): ?>

            <hr>
            <div id="results_routers">

                <?php $key = "router" . $i; ?>

                <p>Router <?php echo $i; ?></p>

                <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Name:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['routers'][$key]['name']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo '<strong style="color:green";>OK</strong>' ?>
                        </div>
                    </div>
                </div>

              <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Interfaces:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['routers'][$key]['if_number']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if ($labServices['routers'][$key]['if_number'] == 0) {
                                  echo '<strong style="color:chocolate;">DEFAULT</strong>';
                                } else {
                                  echo '<strong style="color:green;">OK</strong>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

        <?php endfor; ?>

        <?php for ($i = 1; $i <= sizeof($labServices['servers']); $i++): ?>

            <hr>
            <div id="results_servers">

                <?php $key = "server" . $i; ?>

                <p>Server <?php echo $i; ?></p>

                <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Name:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['servers'][$key]['name']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo '<strong style="color:green";>OK</strong>' ?>
                        </div>
                    </div>
                </div>

              <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Interfaces:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['servers'][$key]['if_number']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if ($labServices['servers'][$key]['if_number'] == 0) {
                                  echo '<strong style="color:chocolate;">DEFAULT</strong>';
                                } else {
                                  echo '<strong style="color:green;">OK</strong>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

        <?php endfor; ?>

        <?php for ($i = 1; $i <= sizeof($labServices['computers']); $i++): ?>

            <hr>
            <div id="results_computers">

                <?php $key = "computer" . $i; ?>

                <p>Computer <?php echo $i; ?></p>

                <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Name:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['computers'][$key]['name']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo '<strong style="color:green";>OK</strong>' ?>
                        </div>
                    </div>
                </div>

              <div style="display: table; width: 30%;">

                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 5px; font-weight: bold; width: 30%">Interfaces:</div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php echo htmlspecialchars($labServices['computers'][$key]['if_number']); ?>
                        </div>
                        <div style="display: table-cell; padding: 5px; width: 30%">
                            <?php
                                if ($labServices['computers'][$key]['if_number'] == 0) {
                                  echo '<strong style="color:chocolate;">DEFAULT</strong>';
                                } else {
                                  echo '<strong style="color:green;">OK</strong>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

        <?php endfor; ?>

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

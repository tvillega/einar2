<?php

$labDataExists  = false;
$queryStringSet = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $labName           = $_POST['lab_name'] ?? '';
  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $labName);
  $labDir            = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized;

  if (!is_dir($labDir)) {
    mkdir($labDir, 0755, true);
  }
    
  $routers   = isset($_POST['routers'])   ? (int)$_POST['routers']   : 0;
  $servers   = isset($_POST['servers'])   ? (int)$_POST['servers']   : 0;
  $switches  = isset($_POST['switches'])  ? (int)$_POST['switches']  : 0;
  $computers = isset($_POST['computers']) ? (int)$_POST['computers'] : 0;

  if ($switches == 0) {
    $switches = 1;
  }

  $data = [
    'lab_name'            => $labName,
    'lab_dir'             => $labDir,
    'routers'             => $routers,
    'servers'             => $servers,
    'computers'           => $computers,
    'switches'            => $switches
  ];

  file_put_contents($labDir . '/lab.json', json_encode($data, JSON_PRETTY_PRINT));

  /* Redirect to myself */
  header("Location: " . $_SERVER['PHP_SELF'] . "?laboratory=" . urlencode($labNameNormalized));

} else if (isset($_GET['laboratory'])) {

  $labNameNormalized = preg_replace('/[^a-zA-Z0-9-]/', '_', $_GET['laboratory']);
  $jsonPath          = $_SERVER['DOCUMENT_ROOT'] . '/labs/' . $labNameNormalized . '/lab.json';
    
  if (file_exists($jsonPath)) {
    $labDataExists  = true;
    $labData        = json_decode(file_get_contents($jsonPath), true);
    $queryStringSet = true;
    $queryString    = '?laboratory=' . urlencode($labNameNormalized);
  }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a new lab</title>
    <style>
        body {
            padding: 20px;
        }
        .form-table {
            display: table;
            margin-bottom: 15px;
        }
        .form-row {
            display: table-row;
        }
        .form-cell {
            display: table-cell;
            padding: 5px;
        }
    </style>
</head>
<body>

    <!-- Send POST with form data to myself (executes the code at the top)  -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <?php if ($queryStringSet): ?>
            <h1>Editing lab <?php echo $labData['lab_name']; ?></h1>
        <?php else: ?>
            <h1>Create a new lab</h1>
        <?php endif; ?>

        <h2>Step 1 of 4</h2>
        <p>Note that you need a switch to connect two devices. Think of it as if you have no crossover cables and surplus of switches.</p>
        
        <div class="form-table">

            <div class="form-row">
                <div class="form-cell">
                    <label for="lab_name">Lab name:</label>
                </div>
                <div class="form-cell">
                    <input type="text" id="lab_name" name="lab_name" 
                           value="<?php echo $labDataExists ? htmlspecialchars($labData['lab_name']) : ''; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-cell">
                    <label for="routers">Number of routers:</label>
                </div>
                <div class="form-cell">
                    <input type="number" id="routers" name="routers" min="0"
                           value="<?php echo $labDataExists ? htmlspecialchars($labData['routers']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell">
                    <label for="servers">Number of servers:</label>
                </div>
                <div class="form-cell">
                    <input type="number" id="servers" name="servers" min="0"
                           value="<?php echo $labDataExists ? htmlspecialchars($labData['servers']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell">
                    <label for="computers">Number of computers:</label>
                </div>
                <div class="form-cell">
                    <input type="number" id="computers" name="computers" min="0"
                           value="<?php echo $labDataExists ? htmlspecialchars($labData['computers']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-cell">
                    <label for="switches">Number of switches:</label>
                </div>
                <div class="form-cell">
                    <input type="number" id="switches" name="switches" min="0"
                           value="<?php echo $labDataExists ? htmlspecialchars($labData['switches']) : ''; ?>"
                           required>
                </div>
            </div>

        </div>

        <button type="submit">Submit</button>
    </form>

    <?php if ($labDataExists): ?>
        <hr>
        <div id="results">

            <h2>Laboratory loaded</h2>
            <p>The following configuration has been saved. To update its values, fill the form and submit it again.</p>

            <div style="display: table; width: 40%;">
                
            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Name:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['lab_name']); ?>
                </div>
            </div>

            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Directory:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['lab_dir']); ?>
                </div>
            </div>

            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Routers:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['routers']); ?>
                </div>
            </div>

            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Servers:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['servers']); ?>
                </div>
            </div>

            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Computers:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['computers']); ?>
                </div>
            </div>

            <div style="display: table-row;">
                <div style="display: table-cell; padding: 5px; font-weight: bold;">Switches:</div>
                <div style="display: table-cell; padding: 5px;">
                    <?php echo htmlspecialchars($labData['switches']); ?>
                </div>
            </div>

            </div>
        </div>
    <?php endif; ?>

    <div style="margin-top: 15px;">
        <a href="/index.php">Home</a>
        <?php if ($queryStringSet): ?>
            | <a href="/setupnetworks.php<?php echo $queryString; ?>">Next (Setup Interfaces)</a>
        <?php else: ?>
            | <span style="color: gray; cursor: not-allowed;" title="Submit a laboratory first">Next (Setup Interfaces)</span>
        <?php endif; ?>
    </div>

</body>
</html>

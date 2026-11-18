<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/webui.json';
if (!file_exists($settingsFile)) {
  die("File webui.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

if (!$settingsData["einar2"]["jailbreak"]) {
  die("Action forbidden: container isolated from host");
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

        <h1>Start a lab</h1>

        <center><p><b>Available laboratories</b></p></center>

        <!-- I have to list the /einar2/labs that should be the DOCUMENT_ROOT I think  -->

        <?php
            echo "<pre>";
            $dirs = scandir($_SERVER["DOCUMENT_ROOT"] . "/labs");
            array_shift($dirs);
            array_shift($dirs);
            echo "</pre>";
        ?>

        <center><p><b>Deployed laboratories</b></p></center>

        <?php
            $command = 'echo exec einar2 labs list all | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
            $response = shell_exec($command);

            $phpArray = [];

            if ($response !== null && trim($response) !== '') {
                $phpArray = json_decode($response, true);
            }

            if (!empty($phpArray) && is_array($phpArray)):
        ?>

                <div style="margin: 0 auto; width: 80%;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ccc; background-color: #f4f4f4;">
                                <th style="padding: 8px;">NAME</th>
                                <th style="padding: 8px;">IMAGE</th>
                                <th style="padding: 8px;">NETWORKS</th>
                                <th style="padding: 8px;">STATE</th>
                                <th style="padding: 8px;"></th>
                            </tr>
                        </thead>
                        <tbody>

        <?php
                foreach ($phpArray as $row):
                    $nameValue     = isset($row['Name']) ? htmlspecialchars($row['Name']) : '';
                    $imageValue    = isset($row['Image']) ? htmlspecialchars($row['Image']) : '';
                    $networksValue = isset($row['Networks']) ? htmlspecialchars($row['Networks']) : '';
                    $stateValue    = isset($row['State']) ? htmlspecialchars($row['State']) : '';
        ?>

                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; vertical-align: middle;"><?php echo $nameValue; ?></td>
                                <td style="padding: 8px; vertical-align: middle;"><?php echo $imageValue; ?></td>
                                <td style="padding: 8px; vertical-align: middle;"><?php echo $networksValue; ?></td>
                                <td style="padding: 8px; vertical-align: middle;">

                                    <?php
                                        if ($stateValue === 'running') {
                                            echo '<span style="color: green; font-weight: bold;">running</span>';
                                        } else {
                                            echo '<span style="color: chocolate; font-weight: bold;">' . $stateValue . '</span>';
                                        }
                                    ?>

                                </td>
                                <td style="padding: 8px; vertical-align: middle; text-align: right;">
                                    <?php if ($nameValue === 'einar2-webui'): ?>
                                        <span style="font-weight: bold; color: black;">WEBUI</span>
                                    <?php else: ?>
                                        <form method="GET" action="" style="margin: 0; display: inline-block;">
                                            <input type="hidden" name="laboratory" value="<?php echo $nameValue; ?>">
                                            <button type="submit" style="padding: 4px 10px; cursor: pointer;">Manage</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
        <?php
                endforeach;
        ?>
                        </tbody>
                    </table>
                </div>
        <?php
            else:
                echo '<p style="text-align: center;">No deployed laboratories found.</p>';
            endif;
        ?>

        <div style="margin-top: 15px;">
            <a href="/index.php">Home</a>
        </div>
    </body>
</html>

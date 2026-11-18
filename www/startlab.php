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

        <?php
            $labsBasePath = $_SERVER['DOCUMENT_ROOT'] . '/labs';
            $availableLabs = [];

            if (is_dir($labsBasePath)) {
                $scanDirs = scandir($labsBasePath);
                foreach ($scanDirs as $dir) {
                    if ($dir === '.' || $dir === '..') continue;

                    $fullPath = $labsBasePath . '/' . $dir;
                    if (is_dir($fullPath)) {
                        $jsonFile = $fullPath . '/lab.json';
                        if (file_exists($jsonFile)) {
                            $jsonContent = file_get_contents($jsonFile);
                            $labData = json_decode($jsonContent, true);
                            if ($labData) {
                                $networksFile = $fullPath . '/networks.json';
                                if (file_exists($networksFile)) {
                                    $netContent = file_get_contents($networksFile);
                                    $netData = json_decode($netContent, true);
                                    if (is_array($netData)) {
                                        $labData['networks_data'] = $netData;
                                    }
                                }

                                $availableLabs[] = $labData;
                            }
                        }
                    }
                }
            }

            if (!empty($availableLabs)):
        ?>
                <div style="margin: 0 auto; width: 80%; margin-bottom: 30px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ccc; background-color: #f4f4f4;">
                                <th style="padding: 8px;">LAB NAME</th>
                                <th style="padding: 8px;">DIRECTORY</th>
                                <th style="padding: 8px;">SWITCHES</th>
                                <th style="padding: 8px;">MACHINES</th>
                                <th style="padding: 8px;"></th>
                            </tr>
                        </thead>
                        <tbody>
        <?php
                foreach ($availableLabs as $lab):
                    $labName  = isset($lab['lab_name']) ? htmlspecialchars($lab['lab_name']) : '';
                    $labDir   = isset($lab['lab_dir']) ? htmlspecialchars($lab['lab_dir']) : '';

                    $switchesHtml = '';
                    if (isset($lab['networks_data']) && is_array($lab['networks_data'])) {
                        $switchLines = [];
                        foreach ($lab['networks_data'] as $sName => $sDetails) {
                            if (isset($sDetails['network'])) {
                                $switchLines[] = htmlspecialchars($sDetails['network']) . ' (net)';
                            }
                            if (isset($sDetails['gateway'])) {
                                $switchLines[] = htmlspecialchars($sDetails['gateway']) . ' (gw)';
                            }
                        }
                        $switchesHtml = implode('<br>', $switchLines);
                    } else {
                        $switchesHtml = isset($lab['switches']) ? htmlspecialchars($lab['switches']) : '';
                    }

                    $machinesHtml = '';
                    if (isset($lab['machines']) && is_array($lab['machines'])) {
                        $machineLines = [];
                        foreach ($lab['machines'] as $mName => $mVal) {
                            $machineLines[] = htmlspecialchars($mName) . ' (' . htmlspecialchars($mVal) . ')';
                        }
                        $machinesHtml = implode('<br>', $machineLines);
                    }
        ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; vertical-align: middle;"><?php echo $labName; ?></td>
                                <td style="padding: 8px; vertical-align: middle; color: #666; font-size: 0.9em;"><?php echo $labDir; ?></td>
                                <td style="padding: 8px; vertical-align: middle; line-height: 1.4;"><?php echo $switchesHtml; ?></td>
                                <td style="padding: 8px; vertical-align: middle; line-height: 1.4;"><?php echo $machinesHtml; ?></td>
                                <td style="padding: 8px; vertical-align: middle; text-align: right;">
                                    <form method="GET" action="" style="margin: 0;">
                                        <input type="hidden" name="laboratory" value="<?php echo $labName; ?>">
                                        <button type="submit" style="padding: 4px 10px; cursor: pointer;">Start</button>
                                    </form>
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
                echo '<p style="text-align: center;">No available laboratories found.</p>';
            endif;
        ?>

        <center><p><b>Deployed laboratories</b></p></center>

        <?php
            $command = 'echo exec einar2 labs list all | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
            $response = shell_exec($command);

            $phpArray = [];

            if ($response !== null && trim($response) !== '') {
                $phpArray = json_decode($response, true);
            }

            $groupedContainers = [];
            $outcastContainersGroupName = 'System / Chores';
            if (is_array($phpArray)) {
                foreach ($phpArray as $row) {
                    $nameValue = isset($row['Name']) ? $row['Name'] : '';

                    // e2-<labname>-something for container grouping
                    if (preg_match('/^e2-(.+?)-/', $nameValue, $matches)) {
                        $labGroupName = $matches[1];
                    } else {

                        $labGroupName = $$outcastContainersGroupName;
                    }

                    $groupedContainers[$labGroupName][] = $row;
                }
            }

            if (!empty($groupedContainers)): // This are tables of containers
                echo '<div style="margin: 0 auto; width: 80%;">';

                foreach ($groupedContainers as $labGroupName => $containers) {
        ?>
                    <details open style="margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; overflow: hidden;">
                        <summary style="padding: 10px 15px; background-color: #f4f4f4; font-family: monospace; font-weight: bold; cursor: pointer; user-select: none;">
                            Lab: <?php echo htmlspecialchars($labGroupName); ?> (<?php echo count($containers); ?> containers)
                        </summary>

                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: monospace; background: #fff;">
                            <thead>
                                <tr style="border-bottom: 2px solid #ccc; background-color: #fafafa;">
                                    <th style="padding: 8px;">NAME</th>
                                    <th style="padding: 8px;">IMAGE</th>
                                    <th style="padding: 8px;">NETWORKS</th>
                                    <th style="padding: 8px;">STATE</th>
                                    <th style="padding: 8px;"></th>
                                </tr>
                            </thead>
                            <tbody>
        <?php
                    foreach ($containers as $row): // This are rows of containers
                        $rawNameValue  = isset($row['Name']) ? $row['Name'] : '';
                        $imageValue    = isset($row['Image']) ? htmlspecialchars($row['Image']) : '';
                        $networksValue = isset($row['Networks']) ? htmlspecialchars($row['Networks']) : '';
                        $stateValue    = isset($row['State']) ? htmlspecialchars($row['State']) : '';

                        $displayName = $rawNameValue;
                        if ($labGroupName != $$outcastContainersGroupName) {
                            $prefix = 'e2-' . $labGroupName . '-';
                            if (strpos($rawNameValue, $prefix) === 0) {
                                $displayName = substr($rawNameValue, strlen($prefix));
                            }
                        }
        ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px; vertical-align: middle;"><?php echo htmlspecialchars($displayName); ?></td>
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

                                        <?php if ($rawNameValue === 'einar2-webui'): ?>
                                            <span style="font-weight: bold; color: black;">WEBUI</span>
                                        <?php else: ?>
                                            <form method="GET" action="" style="margin: 0; display: inline-block;">
                                                <input type="hidden" name="laboratory" value="<?php echo htmlspecialchars($rawNameValue); ?>">
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
                    </details>
        <?php
                }
                echo '</div>';
            else:
                echo '<p style="text-align: center;">No deployed laboratories found.</p>';
            endif;
        ?>

        <div style="margin-top: 15px;">
            <a href="/index.php">Home</a>
        </div>
    </body>
</html>

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

$command = 'echo exec einar2 labs list all | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
$response = shell_exec($command);

$phpArray = [];

if ($response !== null && trim($response) !== '') {
    $phpArray = json_decode($response, true);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start a lab</title>
    <style>
        body {
            padding: 7px;
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
                                <td style="padding: 8px; vertical-align: middle; max-width: 150px;">
                                    <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                        <?php echo $labName; ?>
                                    </div>
                                </td>
                                <td style="padding: 8px; vertical-align: middle; color: #666; font-size: 0.9em; max-width: 250px;">
                                    <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                        <?php echo $labDir; ?>
                                    </div>
                                </td>
                                <td style="padding: 8px; vertical-align: middle; line-height: 1.4; max-width: 200px;">
                                    <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                        <?php echo $switchesHtml; ?>
                                    </div>
                                </td>
                                <td style="padding: 8px; vertical-align: middle; line-height: 1.4; max-width: 200px;">
                                    <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                        <?php echo $machinesHtml; ?>
                                    </div>
                                </td>

                                <td style="padding: 8px; vertical-align: middle; text-align: right; white-space: nowrap;">
                                    <?php
                                        $startCommand = 'exec einar2 labs start ' . $labName;
                                        $stopCommand  = 'exec einar2 labs stop ' . $labName;
                                    ?>

                                    <button onclick="callStreamingEinarDaemon(event, '<?php echo htmlspecialchars($startCommand, ENT_QUOTES); ?>')" style="padding: 4px 10px; cursor: pointer; margin-right: 5px;">
                                        Start
                                    </button>

                                    <button onclick="callStreamingEinarDaemon(event, '<?php echo htmlspecialchars($stopCommand, ENT_QUOTES); ?>')" style="padding: 4px 10px; cursor: pointer; margin-right: 5px;">
                                        Stop
                                    </button>

                                    <form method="GET" action="/createlab.php" style="margin: 0; display: inline-block;">
                                        <input type="hidden" name="laboratory" value="<?php echo htmlspecialchars($labName); ?>">
                                        <button type="submit" style="padding: 4px 10px; cursor: pointer;">Edit</button>
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
            $groupedContainers = [];
            $outcastContainersGroupName = 'System / Chores';
            if (is_array($phpArray)) {
                foreach ($phpArray as $row) {
                    $nameValue = isset($row['Name']) ? $row['Name'] : '';

                    // e2-<labname>-something for container grouping
                    if (preg_match('/^e2-(.+?)-/', $nameValue, $matches)) {
                        $labGroupName = $matches[1];
                    } else {

                        $labGroupName = $outcastContainersGroupName;
                    }

                    $groupedContainers[$labGroupName][] = $row;
                }
            }

            if (!empty($groupedContainers)): // This are tables of containers
                echo '<div style="margin: 0 auto; width: 80%;">';

                foreach ($groupedContainers as $labGroupName => $containers) {
        ?>
                    <details style="margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; overflow: hidden;">
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
                        if ($labGroupName != $outcastContainersGroupName) {
                            $prefix = 'e2-' . $labGroupName . '-';
                            if (strpos($rawNameValue, $prefix) === 0) {
                                $displayName = substr($rawNameValue, strlen($prefix));
                            }
                        }
        ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px; vertical-align: middle; max-width: 200px;">
                                        <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                            <?php echo htmlspecialchars($displayName); ?>
                                        </div>
                                    </td>
                                    <td style="padding: 8px; vertical-align: middle; max-width: 250px;">
                                        <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                            <?php echo $imageValue; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 8px; vertical-align: middle; max-width: 200px;">
                                        <div style="overflow-x: auto; white-space: nowrap; max-width: 100%;">
                                            <?php echo $networksValue; ?>
                                        </div>
                                    </td>
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
                                            <?php
                                                $isRunning = ($stateValue === 'running');
                                                $btnStyle = 'padding: 4px 10px; cursor: ' . ($isRunning ? 'pointer' : 'not-allowed') . ';';
                                                if (!$isRunning) {
                                                    $btnStyle .= ' opacity: 0.5; background-color: #eee; color: #888; border: 1px solid #ccc;';
                                                }
                                            ?>
                                            <button onclick="callEinarDaemon(event, 'exec einar2 labs open <?php echo htmlspecialchars($rawNameValue, ENT_QUOTES); ?>')" style="<?php echo $btnStyle; ?>" <?php echo $isRunning ? '' : 'disabled'; ?>>Open</button>
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


        <!-- callFancyEinarDaemon loading screen -->
        <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.75); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; font-family: monospace;">
            <div style="border: 4px solid #f3f3f3; border-top: 4px solid #333; border-radius: 50%; width: 40px; height: 40px; animation: spin 0.8s linear infinite; margin-bottom: 12px;"></div>
            <div style="color: #333; font-size: 0.95em; font-weight: bold;">Applying changes...</div>
        </div>

        <!-- callStreamingEinarDaemon loading screen + terminal output -->
        <div id="streaming-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; font-family: monospace; padding: 20px; box-sizing: border-box;">
            <div style="border: 4px solid #333; border-top: 4px solid #fff; border-radius: 50%; width: 35px; height: 35px; animation: spin 0.8s linear infinite; margin-bottom: 12px;"></div>
            <div style="color: #fff; font-size: 0.95em; font-weight: bold; margin-bottom: 20px; letter-spacing: 0.5px;">Applying changes...</div>
            <pre id="terminal-output" style="width: 100%; max-width: 700px; height: 250px; background: #111; color: #00ff66; border: 1px solid #333; border-radius: 4px; padding: 12px; overflow-y: auto; text-align: left; font-size: 0.85em; margin: 0; white-space: pre-wrap; word-break: break-all;"></pre>
        </div>

        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
    </body>
    <script>
    function callEinarDaemon(event, payload) {
        event.preventDefault();

        fetch('/startprogram.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'payload=' + encodeURIComponent(payload)
        })
        .then(response => response.text())
        .then(data => {
            window.location.reload();
        })
        .catch(error => { console.error('Error:', error); });
    }

    function callFancyEinarDaemon(event, payload) {
        event.preventDefault();
        document.getElementById('loading-overlay').style.display = 'flex';

        fetch('/startprogram.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'payload=' + encodeURIComponent(payload)
        })
        .then(response => response.text())
        .then(data => {
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loading-overlay').style.display = 'none';
        });
    }

    async function callStreamingEinarDaemon(event, payload) {
        event.preventDefault();

        const overlay = document.getElementById('streaming-overlay');
        const terminal = document.getElementById('terminal-output');

        terminal.textContent = '';
        overlay.style.display = 'flex';

        try {
            const response = await fetch('/startstream.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'payload=' + encodeURIComponent(payload)
            });

            if (!response.body) throw new Error('ReadableStream not supported.');

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;

                terminal.textContent += decoder.decode(value, { stream: true });
                terminal.scrollTop = terminal.scrollHeight;
            }

            const reloadTimeout = <?php echo (int)($settingsData['eworker']['timeout'] ?? 5000); ?>;

            setTimeout(() => {
                window.location.reload();
            }, reloadTimeout);

        } catch (error) {
            console.error('Error:', error);
            terminal.textContent += '\n[Stream error: ' + error.message + ']';
            setTimeout(() => { overlay.style.display = 'none'; }, 8000);
        }
    }
    </script>
</html>

<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/settings.json';
if (!file_exists($settingsFile)) {
  die("File settings.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

?>


<!DOCTYPE html>
<html>
    <head>
        <title>Einar2</title>
    </head>
        <style>
            hr {
                border: 1.3px dashed;
                margin: 0.1em;
            }
            p { margin:0 }
        </style>
    <body>

        <h1>Welcome to Einar 2.0</h1>
        <center><p><b>Tasks</b></p></center>

        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Start a lab<span></p>
                    <?php else: ?>
                        <p><a href="/startlab.php">Start a lab</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>Start a new lab</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/createlab.php">Create a lab</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Create a custom lab</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Not available.">Edit a lab</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>Edit an existing lab</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Running labs<span></p>
                    <?php else: ?>
                        <p><a href="/runninglabs.php">Running labs</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>View and stop running labs</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Not available.">Import a lab</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>Import a lab from USB or internet</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/releasenotes.php">Release notes</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>View the release notes for this release</p>
                </div>
            </div>
        </div>
        <hr>
        <br>

        <center><p><b>Configurations</b></p></center>

        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/archetypes/">Archetypes</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Docker compose files and blocks written in simplified Tera template engine.</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/docs/index.php">Documentation</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>A snapshot of the Einar2 website</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Legacy feat from Einar1.">Setup a firewall</span></p>
                </div>
                <div style="display: table-cell;"> 
                    <p>The webserver is running as root. This will set up iptables to prevent others from reaching it.</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Legacy feat from Einar1.">Renew ip</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>There is no IP set by default, this will try to renew with DHCP. Consider setting up the firewall first.</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Terminal<span></p>
                    <?php else: ?>
                        <p><a href="/startprogram.php?program=lxterminal">Terminal</a></p>
                    <?php endif; ?>

                </div>
                <div style="display: table-cell;">
                    <p>Gives you a terminal to the host</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Wireshark<span></p>
                    <?php else: ?>
                        <p><a href="/startprogram.php?program=wireshark">Wireshark</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;"> 
                    <p>Starts wireshark to analyze network traffic</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Featherpad<span></p>
                    <?php else: ?>
                        <p><a href="/startprogram.php?program=featherpad">Featherpad</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>Starts a texteditor that looks a lot like old school notepad in Windows</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Legacy feat from Einar1.">Keyboard layout</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>The default keyboard layout is US, this will change to Swedish</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Legacy feat from Einar1.">Click to focus</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>Change window behavior from focus follows mouse</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><span style="color: gray; cursor: not-allowed;" title="Legacy feat from Einar1.">Shutdown</span></p>
                </div>
                <div style="display: table-cell;">
                    <p>Shutdown the computer</p>
                </div>
            </div>
        </div>
        <hr>
    </body>
</html>

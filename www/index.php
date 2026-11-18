<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/webui.json';
if (!file_exists($settingsFile)) {
  die("File webui.json missing at document root");
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
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Manage labs<span></p>
                    <?php else: ?>
                        <p><a href="/startlab.php">Manage labs</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>View running and stopped labs</p>
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
                    <p><a href="https://github.com/tvillega/einar2/releases">Release notes</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Check out what's new on Einar2!'</p>
                </div>
            </div>
        </div>
        <hr>
        <br>

        <center><p><b>Programs</b></p></center>

        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Terminal<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'launch terminal')">Terminal</a></p>
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
                        <p><a href="" onClick="callEinarDaemon(event, 'launch wireshark')">Wireshark</a></p>
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
                                 title="Only available on jailbroken Einar2">Notepad<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'launch notepad')">Notepad</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>Starts a texteditor that looks a lot like old school notepad in Windows</p>
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
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Edit webui<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'exec eterm')">Edit webui</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>Starts wireshark to analyze network traffic</p>
                </div>
            </div>
        </div>
        <hr>

        <center><p><b>Chores</b></p></center>

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
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Renew ip address<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'exec dhcpcd')">Renew ip address</a></p>
                    <?php endif; ?>

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
                                 title="Only available on jailbroken Einar2">Reset faulty DNS<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'exec dnsfix')">Reset faulty DNS</a></p>
                    <?php endif; ?>

                </div>
                <div style="display: table-cell;">
                    <p>Systemd overwrites resolv.conf, this will try to reset it to 8.8.8.8</p>
                </div>
            </div>
        </div>
        <hr>
        <!-- I REFUSE TO REMOVE THIS SECTION -- this is such an Einar classic but reversed hahha -->
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Keyboard layout<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'exec setxkbmap se')">Keyboard layout</a></p>
                    <?php endif; ?>
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
                    <?php if (!$settingsData['einar2']['jailbreak']): ?>
                        <p><span style="color: gray; cursor: not-allowed;"
                                 title="Only available on jailbroken Einar2">Shutdown<span></p>
                    <?php else: ?>
                        <p><a href="" onClick="callEinarDaemon(event, 'exec shutdown')">Shutdown</a></p>
                    <?php endif; ?>
                </div>
                <div style="display: table-cell;">
                    <p>Shutdown the computer</p>
                </div>
            </div>
        </div>
        <hr>
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
            .then(data => {})
            .catch(error => {console.error('Error:', error);});
        }
    </script>
</html>

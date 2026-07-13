<!DOCTYPE html>
<html>
    <head>
        <title>Einar</title>
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
                    <p><a href="/startlab.php">Start a lab</a></p>
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
                    <p><a href="/runninglabs.php">Running labs</a></p>
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
                    <p><a href="/importlab.php">Import a lab</a></p>
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
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/einar2/index.php">Documentation</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>A snapshot of Einar2 website</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/setupfirewall.php">Setup a firewall</a></p>
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
                    <p><a href="/renewip.php">Renew ip</a></p>
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
                    <p><a href="/startprogram.php?program=xterm">Xterm</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Gives you a terminal to dom0</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/startprogram.php?program=ethereal">Ethereal</a></p>
                </div>
                <div style="display: table-cell;"> 
                    <p>Starts ethereal to analyze network traffic</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/startprogram.php?program=leafpad">Leafpad</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Starts a texteditor that looks a lot like notepad in Windows</p>
                </div>
            </div>
        </div>
        <hr>
        <div style="width: 100%; display: table;">
            <div style="display: table-row">
                <div style="width: 160px; display: table-cell;">
                    <p><a href="/kb.php">Keyboard layout</a></p>
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
                    <p><a href="/click.php">Click to focus</a></p>
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
                    <p><a href="/shutdown.php">Shutdown</a></p>
                </div>
                <div style="display: table-cell;">
                    <p>Shutdown the computer</p>
                </div>
            </div>
        </div>
        <hr>
    </body>
</html>

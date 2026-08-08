<?php

require_once __DIR__ . '../../../vendor/Michelf/MarkdownExtra.inc.php';

$downloadIndex          = true;
$markdownTextChangelog  = file_get_contents(__DIR__ . '/../assets/releasenotes.md');
$markdownTextChangelog2 = str_replace("# Changelog", '', $markdownTextChangelog);
$markdownTextChangelog3 = str_replace("##", "###", $markdownTextChangelog2);

include(__DIR__ . '/../assets/header.php');

?>

<h2>Index</h2>
<ul>
    <li><a href="#latest">Latest build</a></li>
    <li><a href="#demo">Demo movie</a></li>
    <li><a href="#changelog">Changelog</a></li>
</ul>

<h2 id="latest">Latest build</h2>
<p>The image <a href="https://hub.docker.com/r/tvillega/einar2">tvillega/einar2:latest</a> is available on Docker Hub.</p>
<p>You will need a Linux device with enough privileged permissions to run the simulations,
instructions to generated an Einar2 VM are provided in the advanced user guide.</p>

<h2 id="demo">Demo movie</h2>
<p>Coming soon!</p>

<h2 id="warning">Warning!</h2>
<p>If you are using an Einar2 VM, you'll have complete control over the Linux system.
For security reasons please only perform activities related to your simulations (this IS NOT a distro).</p>

<?php

echo '<h2 id="changelog">Changelog</h2>';
echo Michelf\MarkdownExtra::defaultTransform($markdownTextChangelog3);
include(__DIR__ . '/../assets/footer.php');

?>

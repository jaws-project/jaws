<?php
/**
 * index.php - Tests index
 *
 * @version  $Id $
 * @author   Jonathan Hernandez <ion@gluch.org.mx>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Piwi
 */
echo "<html>
<head>
<title>Piwi Tests</title>
</head>
<body>
<h1>Piwi Tests</h1>";
foreach (glob("*.php") as $filename) {
    if ($filename != 'index.php') {
        echo "<li><a href=\"{$filename}\">{$filename}</a></li>";
    }
}
echo "</body>
</html>";
?>

<?php
/**
 * A simple script to build a single js file from the multiple sources
 * @license    http://www.opensource.org/licenses/lgpl-license.php  LGPL
 */
// simple script contains a merged js file from multiple source files
// can optionaly strip whitespace

require_once 'HTML/AJAX.php';
$dest = "HTML_AJAX.js";
if (isset($argv[1])) {
    $dest = $argv[1];
}
$strip = false;
if (isset($argv[2]) && $argv[2] == 'strip') {
    $strip = true;
}

$source = array('Compat.js','Main.js','Queue.js', 'clientPool.js',  'IframeXHR.js', 'serializer/UrlSerializer.js','serializer/phpSerializer.js','Dispatcher.js','HttpClient.js','Request.js','serializer/JSON.js','serializer/haSerializer.js','Loading.js','util.js','behavior/behavior.js','behavior/cssQuery-p.js');

$out = '';
$ajax = new HTML_AJAX();
foreach($source as $file) {
    if ($strip) {
        $s = $ajax->packJavaScript(file_get_contents($file));
    }
    else {
        $s = file_get_contents($file);
    }

    $out .= "// $file\n";
    $out .= $s;
}

$fp = fopen($dest,'w');
fwrite($fp,$out);
fclose($fp);
?>

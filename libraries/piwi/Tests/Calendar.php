<?php
header('Content-Type: text/html; charset=utf-8'); //magic, big fix
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Entries</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../piwidata/css/default.css" type="text/css" />
<link rel="stylesheet" href="../piwidata/js/jscalendar/calendar-win2k-1.css" type="text/css" />

</head>
<body>
<?php
if (!defined('YES')) {
    define('YES', 1);
}
if (!defined('NO')) {
    define('NO', 0);
}

if (!defined('PIWI_CREATE_PIWIXML')) {
    define('PIWI_CREATE_PIWIXML', 'no');
}

if (!defined('PIWI_LOAD')) {
    define('PIWI_LOAD', 'SMART');
}

if (!defined('PIWI_URL')) {
    define('PIWI_URL', '/');
}
include_once '../Piwi.php';

$calendar =& Piwi::CreateWidget('DatePicker', 'calendar');
//$calendar->addSelectedDate('20060411');
//$calendar->addSelectedDate('2006-01-10');
$calendar->setLanguageCode('es', true);
$calendar->setDateFormat("%A, %d de %B, %Y");
//$calendar->selectMultipleDates();
$calendar->setButtonIcon(STOCK_CALENDAR);
$calendar->show();
?>
</body>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Entries</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="../piwidata/css/default.css" type="text/css" />
</head>
<body>
<?php
exit; //for run this test, please remove this line
define('PIWI_URL', '../');
include_once '../Piwi.php';


$vbox = new VBox();

$vbox->packStart(new Entry('entry1', '', 'Entry'));
$vbox->packStart(new TextArea('textarea1', '', 'Text Area'));
//$vbox->packStart(new Calendar('calendar1', '', 'Calendar Entry'));

$checkButtons = new CheckButtons('checkbuttons1', 'vertical');
$checkButtons->setTitle('Check Buttons');
$checkButtons->addOption('Foo', 'FOO');
$checkButtons->addOption('Bar', 'BAR');
$checkButtons->addOption('Waz', 'WAZ');
$vbox->packStart($checkButtons);

$radioButtons = new RadioButtons('radiobuttons1', 'vertical');
$radioButtons->setTitle('Radio Buttons');
$radioButtons->addOption('Foo', 'FOO');
$radioButtons->addOption('Bar', 'BAR');
$radioButtons->addOption('Waz', 'WAZ');
$vbox->packStart($radioButtons);

//$file = new FileEntry('archivo');
//$vbox->packStart($file);

$b = new Button('b1', 'Send', STOCK_CANCEL);
$b->addEvent(new JSEvent(ON_CLICK, 'this.form.submit()'));
$vbox->packStart($b);
$form  = new Form($_SERVER['PHP_SELF'], 'post');
$form->add($vbox);
$form->show();

if (count($_POST) > 1) {
    $data = array();
    $i = 0;
    foreach ($_POST as $k => $v) {
        $data[$i]['name'] = $k;
        if (is_array($v)) {
            $data[$i]['value'] = implode('<br />', $v);
        } else {
            $data[$i]['value'] = $v;
        }
        $i++;
    }
    $grid = new DataGrid($data, 'Widgets/Values');
    $grid->addColumn(new Column('Widget', 'name'));
    $grid->addColumn(new Column('Value', 'value'));
    $grid->show();
}
?>
</body>
</html>

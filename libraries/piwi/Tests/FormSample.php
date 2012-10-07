<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Form Sample</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="../piwidata/css/default.css" type="text/css" />
<link rel="stylesheet" href="../piwidata/js/jscalendar/calendar-win2k-1.css" type="text/css" />
</head>
<body>
<?php
define('PIWI_URL', '../');

include_once '../Piwi.php';

/*Field set*/
$entryPC = new Entry('codigo', '', 'Código Postal');
$entryPC->setComment('Código Postal');

$fieldset = new FieldSet('Dirección');
$fieldset->add($entryPC);
$fieldset->add(new Entry('colonia', '', 'Colonia'));


/*A ComboGroup*/
$combogroup = new ComboGroup('opciones2', 'Muchas opciones');

$pares = array();
$impares = array();
for ($i = 0; $i < 20; $i++) {
    if ($i % 2 == 0) {
        $pares[] = new ComboOption("numero $i", "Número $i");
    } else {
        $impares[] =  new ComboOption("$numero $i", "Número $i");
    }
}

$combogroup->addGroup('pares', 'pares', $pares);
$combogroup->addGroup('impares', 'impares', $impares, true);
$combogroup->setDefault('Número 2');
$combogroup->setComment('numeros');
/*Text Area*/

$fieldset2 = new FieldSet('Botones');
$fieldset2->add(new Button('suma', 'suma'));
$fieldset2->add(new Button('resta', 'resta'));

$main_box = new HBox();
$left = new VBox();
$right = new VBox();

$main_box->packStart($left);
$main_box->packStart($right);

$entry = new Entry('apellido_1', '', 'Apellido Paterno');
$entry->setComment('Apellido de tu jefe');

$entry2 = new Entry('nombre', '', 'Nombre');
$entry2->setComment('Apellido de tu abuelita');

$left->packStart($entry2);
$left->packStart($entry);
$left->packStart(new Entry('apellido_2', '', 'Apellido Materno'));


/*right side*/

/*right up*/
$right_up = new HBox();
$right_up_left = new VBox();
$right_up_right = new VBox();
$right_up->packStart($right_up_left);
$right_up->packStart($right_up_right);

/*Right up left*/
$right_up_left->packStart(new Button ('hola', 'Hola'));
$right_up_left->packStart(new DatePicker('hola', '', '', STOCK_CALENDAR));

$colorPicker = new ColorPicker('color', '', '', STOCK_COLORSELECT);
$colorPicker->usePopup();
$right_up_left->packStart($colorPicker);
/*Right up right*/
$right_up_right->packStart(new TextArea('textarea', 'Hola Mundo!'));

/*right down*/
$right_down = new HBox();
$right_down->packStart(new Button('picame', 'picame'));
$right_down->packStart(new Button('sueltame', 'sueltame'));
$right_down->packStart($fieldset);
$right_down->packStart($combogroup);

$right->packStart($right_up);
$right->packStart($right_down);

$form  = new Form('hola.php');
$form->add($main_box);
$form->show();
?>
</body>
</html>

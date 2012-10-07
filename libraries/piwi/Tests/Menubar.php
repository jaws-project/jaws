<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Menubar</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="../piwidata/css/default.css" type="text/css" />
<link rel="stylesheet" href="example1.css" type="text/css" />
</head>
<body>
<?php

define('PIWI_URL', '../');

if (!defined('PIWI_LOAD')) {
    define('PIWI_LOAD', 'SMART');
}
include_once '../Piwi.php';

$menu = Piwi::CreateWidget('MenuBar');
$menu->add('Archivo');
$menu->add('Archivo/Abrir');
$menu->add('Archivo/Cerrar');
$menu->add('Archivo/Guardar');
$menu->add('Archivo/Guardar/Guardar como archivo');
$menu->add('Archivo/Guardar/Guardar como PDF');
$menu->add('Editar');
$menu->add('Editar/Copiar');
$menu->add('Editar/Cortar');
$menu->add('Editar/Pegar');
$menu->add('Editar/Pegar/Especial');
$menu->add('Editar/Pegar/Normal');
$menu->add('Ayuda');
$menu->add('Ayuda/Acerca de...');
$menu->show();
?>
</body>
</html>

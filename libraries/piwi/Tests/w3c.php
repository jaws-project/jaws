<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>XHTML Reference</title>
<link rel="SHORTCUT ICON" href="/images/favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta name="Keywords" content="XML,tutorial,HTML,DHTML,CSS,XSL,XHTML,JavaScript,ASP,ADO,VBScript,DOM,authoring,programming,training,learning,beginner's guide,primer,lessons,school,howto,reference,examples,samples,source code,tags,demos,tips,links,FAQ,tag list,forms,frames,color table,W3C,Cascading Style Sheets,Active Server Pages,Dynamic HTML,Internet,database,development,Webbuilder,Sitebuilder,Webmaster,HTMLGuide,SiteExpert,iis" />
<meta name="Description" content="HTML,CSS,JavaScript,DHTML,XML,XHTML,ASP,ADO and VBScript tutorial from W3Schools." />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<link rel="stylesheet" href="piwidata/css/default.css" type="text/css" />
</head>
<body>
<?php
include_once "Piwi.php";

$button = new Button ("boton", "Boton");
$button->SetSubmit ();
$button->SetStock (STOCK_CANCEL);
$button->AddEvent (new JSEvent (ON_CLICK, "javascript:alert('huevudisimo');"));
//$button->Show ();

$entry = new Entry ("cajita", "Una cosa");
$entry->AddEvent (new JSEvent (ON_CHANGE, "javascript:alert(this.value)"));
$entry->AddEvent (new JSEvent (ON_CHANGE, "javascript:alert(calcMD5(this.value));",
                               "http://jaws.com.mx/templates/controlpanel/md5.js"));
//$entry->Show ();


$combo = new Combo ("opciones");
$combo->AddOption ("Jaws Proyect", "jawsproject");
$combo->AddOption ("Piwi Proyect", "piwiproject");
$combo->AddOption ("mBloggy", "mbloggy");
$combo->SetDefault ("mbloggy");
//$combo->Show ();

$combogroup = new ComboGroup ("opciones2");

$pares = array ();
$impares = array ();
for ($i=0; $i < 20; $i++) {
    if ($i % 2 == 0)
        $pares[] = new ComboOption ("numero $i", "N�mero $i");
    else
        $impares[] =  new ComboOption ("numero $i", "N�mero $i");
}

$combogroup->AddGroup ("pares", "pares", $pares);
$combogroup->AddGroup ("impares", "impares", $impares, true);
$combogroup->SetDefault ("N�mero 2");
//$combogroup->Show ();

$comboimage = new ComboImage ("comboimage");
$comboimage->AddOption ("Jaws Proyect", "jawsproject", STOCK_CANCEL);
$comboimage->AddOption ("Piwi Proyect", "piwi", STOCK_CANCEL);
$comboimage->AddOption ("mBloggy", "mbloggy", "http://www.w3schools.com/images/dnicon.gif");
$comboimage->SetDefault ("mbloggy");
//$comboimage->Show ();



$textarea = new TextArea ("textarea", "Hola Mundo!");
//$textarea->Show ();


$radio = new RadioButtons ("favoritos", "vertical");
$radio->AddOption ("Jaws Project", "jawsproject");
$radio->AddOption ("Piwi Project", "piwiproject");
$radio->SetDefault ("piwiproject");
//$radio->Show ();

$check = new CheckButtons ("favoritos2", "vertical");
$check->AddOption ("Jaws Project", "jawsproject");
$check->AddOption ("Piwi Project", "piwiproject");
$check->SetDefault ("piwiproject");

$spin = new SpinButton ("spin", 10);
$spin->SetDefault (3);
$vbox = new VBox ();
$vbox->PackStart ($comboimage);
$vbox->PackEnd ($entry);
$vbox->PackStart ($check);
$hbox = new HBox ();
$hbox->SetSpacing (3);
$hbox->PackStart ($button);
$hbox->PackStart ($radio);
$hbox->PackEnd ($vbox);
//$hbox->Show ();

$toolbar = new Toolbar ();
$toolbar->Add ($button);
$toolbar->Add ($entry);
$toolbar->Add ($comboimage);
$toolbar->Add ($spin);
$toolbar->SetStyle ("background-color: #331");
$toolbar->SetSpacing (0);

$editor = new VBox ();
$editor->PackStart ($toolbar);
$editor->PackStart ($textarea);

$viewport = new ViewPort ("viewport", 600, 180);
$viewport->Add ($editor);
//$viewport->Show ();

$menu = new MenuBar ();
$menu->Add ("Archivo");
$menu->Add ("Archivo/Abrir");
$menu->Add ("Archivo/Cerrar");
$menu->Add ("Archivo/Guardar");
$menu->Add ("Archivo/Guardar/Guardar como archivo");
$menu->Add ("Archivo/Guardar/Guardar como PDF");
$menu->Add ("Editar");
$menu->Add ("Editar/Copiar");
$menu->Add ("Editar/Cortar");
$menu->Add ("Editar/Pegar");
$menu->Add ("Editar/Pegar/Especial");
$menu->Add ("Editar/Pegar/Normal");
$menu->Add ("Ayuda");
$menu->Add ("Ayuda/Acerca de...");
$menu->Show ();
echo ("<br/><br/><br/><br/><br/>");
$cal = new Calendar ();
$cal->SetDisplayWeekNumber(false);
$cal->SetDisplayToday(false);
$cal->AddHoliday(9, 2, 2005, "Cumple de Norma y Jorge");
$cal->Show ();
echo ("<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>");
$cal2 = new Calendar ();
$cal2->SetDisplayWeekNumber(false);
$cal2->SetDisplayToday(false);
$cal2->AddHoliday(9, 2, 2005, "Cumple de Norma y Jorge");
$cal2->Show ();
?>
</body>
</html>
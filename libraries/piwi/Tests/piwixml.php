<?php
include_once "Piwi.php";

$button = new Button ("boton", "Boton");
$button->SetSubmit ();
$button->SetStock (STOCK_CANCEL);
$button->AddEvent (new JSEvent (ON_CLICK, "javascript:alert('huevudisimo');"));

echo $button->GetPiwiXML ();

echo "\n\n\n";

$combo = new Combo ("opciones");
$combo->AddOption ("Jaws Proyect", "jawsproject");
$combo->AddOption ("Piwi Proyect", "piwiproject");
$combo->AddOption ("mBloggy", "mbloggy");
$combo->SetDefault ("mbloggy");

echo $combo->GetPiwiXML ();

echo "\n\n\n";

$combogroup = new ComboGroup ("opciones2");

$pares = array ();
$impares = array ();
for ($i=0; $i < 20; $i++) {
    if ($i % 2 == 0)
        $pares[] = new ComboOption ("numero $i", "Número $i");
    else
        $impares[] =  new ComboOption ("numero $i", "Número $i");
}

$combogroup->AddGroup ("pares", "pares", $pares);
$combogroup->AddGroup ("impares", "impares", $impares, true);
$combogroup->SetDefault ("pares", "Número 2");

echo $combogroup->GetPiwiXML ();

echo "\n\n\n";

$textarea = new TextArea ("textarea", "Texto");

echo $textarea->GetPiwiXML ();

echo "\n\n\n";

$entry = new Entry ("cajita", "Una cosa");
$entry->AddEvent (new JSEvent (ON_CHANGE, "javascript:alert(this.value)"));
$entry->AddEvent (new JSEvent (ON_CHANGE, "javascript:alert(calcMD5(this.value));",
                               "http://jaws.com.mx/templates/controlpanel/md5.js"));
echo $entry->GetPiwiXML ();

echo "\n\n\n";

$comboimage = new ComboImage ("comboimage");
$comboimage->AddOption ("Jaws Proyect", "jawsproject", STOCK_CANCEL);
$comboimage->AddOption ("Piwi Proyect", "piwi", STOCK_CANCEL);
$comboimage->AddOption ("mBloggy", "mbloggy", "http://www.w3schools.com/images/dnicon.gif");
$comboimage->SetDefault ("mbloggy");

echo $comboimage->GetPiwiXML ();

echo "\n\n\n";

$radio = new RadioButtons ("favoritos", "vertical");
$radio->AddOption ("Jaws Project", "jawsproject");
$radio->AddOption ("Piwi Project", "piwiproject");
$radio->SetDefault ("piwiproject");

echo $radio->GetPiwiXML ();

echo "\n\n\n";

$check = new CheckButtons ("favoritos2", "vertical");
$check->AddOption ("Jaws Project", "jawsproject");
$check->AddOption ("Piwi Project", "piwiproject");
$check->SetDefault ("piwiproject");

echo $check->GetPiwiXML ();

echo "\n\n\n";

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
$menu->Add ("Archivo/Guardar/Destrozar");

echo $menu->GetPiwiXML ();

echo "\n\n\n";

$toolbar = new Toolbar ();
$toolbar->Add ($button);
$toolbar->Add ($entry);
$toolbar->Add ($comboimage);
$toolbar->SetStyle ("background-color: #331");
$toolbar->SetSpacing (0);

echo $toolbar->GetPiwiXML ();

echo "\n\n\n";

$vbox = new VBox ();
$vbox->PackStart ($comboimage);
$vbox->PackEnd ($entry);
$vbox->PackStart ($check);
$hbox = new HBox ();
$hbox->SetSpacing (3);
$hbox->PackStart ($button);
$hbox->PackStart ($radio);
$hbox->PackEnd ($vbox);

echo $hbox->GetPiwiXML ();

echo "\n\n\n";

$editor = new VBox ();
$editor->PackStart ($toolbar);
$editor->PackStart ($textarea);

echo $editor->GetPiwiXML ();
echo "\n\n\n";

$viewport = new ViewPort ("viewport", 600, 180);
$viewport->Add ($editor);

echo $viewport->GetPiwiXML ();
echo "\n\n\n";

$hidden_field = new HiddenEntry ("nombre", "valor");
echo $hidden_field->GetPiwiXML ();
echo "\n\n\n";


?>
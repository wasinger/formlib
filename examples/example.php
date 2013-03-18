<?php
namespace Wa72\Formlib;
if (file_exists(__DIR__ . "/../vendor/autoload.php")) require __DIR__ . "/../vendor/autoload.php";
else require __DIR__ . "/../../../../vendor/autoload.php";

$form = new Form('testform');

$emailfield = new FieldInputEmail('email', 'E-Mail', '', 'Bitte geben Sie Ihre E-Mail-Adresse ein!');
$emailfield->setAttribute('placeholder', 'name@beispiel.de')->setRequired(true);
$form->add($emailfield);

$form->add(new FieldInputText('name'));
$form->get('name')->setLabel('Name')->setRequired(true)->setAttribute('size', 50);


$form->add(new FieldTextarea('kommentar'));

$select = new FieldSelect('zufrieden', array(1 => 'Ja', 2 => 'Nein', 3 =>'geht so'), '');
$select->setLabel('sind Sie zufrieden?')->setRequired(true);
$form->add($select);

$plz = new FieldInputText('plz', 'PLZ');
$plz->setPattern('^7207[0246]$', 'Dies ist keine gültige Tübinger PLZ', 'Geben Sie eine Postleitzahl aus dem Bereich 72070 bis 72076 ein', '7207x');

$form->add($plz);

$form->get('kommentar')->setAttribute('placeholder', 'Geben Sie hier Ihren Kommentar ein')
->setAttribute('rows', 6)->setAttribute('cols', 70);

$form->add(new FieldInputCheckbox('agb', 'Ich stimme den AGB zu', 'ja'));
$form->get('agb')->setRequired(true, 'Sie müssen den AGB zustimmen');

$form->add(new FieldInputRadioGroup('farbe', array('rot' => 'Rot', 'gruen' => 'Grün', 'gelb' => 'Gelb')));

$tage = array('mo' => 'Montag', 'di' => 'Dienstag', 'mi' => 'Mittwoch', 'do' => 'Donnerstag');


$form->add(FieldInputCheckboxGroup::createInputCheckboxGroup('Wochentag', $tage)->setRequired(true, 'Bitte geben Sie mindestens einen Wochentag an'));

$form->add(FieldSelect::createSelect('monate', array('Januar', 'Februar', 'März', 'April'), 'bitte auswählen'));

if (isset($_REQUEST['testform'])) {
    $form->bind($_REQUEST);
    if ($form->isValid()) {
        $formdom = $form->renderResultToDom();
        var_dump($form->getData(false));
        var_dump($form->getData(true));
        echo $formdom->ownerDocument->saveHTML();
        exit;
    }
}

$formdom = $form->renderToDom();

$formdom->setAttribute('noValidate', true);

$submit = $formdom->ownerDocument->createElement('input');
$submit->setAttribute('type', 'submit');
$formdom->appendChild($submit);

echo $formdom->ownerDocument->saveHTML();



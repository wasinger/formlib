<?php
namespace Wa72\Formlib;
use Wa72\Formlib\Field\InputCheckbox;
use Wa72\Formlib\Field\InputCheckboxGroup;
use Wa72\Formlib\Field\InputEmail;
use Wa72\Formlib\Field\InputRadioGroup;
use Wa72\Formlib\Field\InputText;
use Wa72\Formlib\Field\Select;
use Wa72\Formlib\Field\Textarea;

if (file_exists(__DIR__ . "/../vendor/autoload.php")) require __DIR__ . "/../vendor/autoload.php";
else require __DIR__ . "/../../../../vendor/autoload.php";

$form = new Form('testform');

$emailfield = new InputEmail('email', 'E-Mail', '', 'Bitte geben Sie Ihre E-Mail-Adresse ein!');
$emailfield->setAttribute('placeholder', 'name@beispiel.de')->setRequired(true);
$form->add($emailfield);

$form->add(new InputText('name'));
$form->get('name')->setLabel('Name')->setRequired(true)->setAttribute('size', 50);


$form->add(new Textarea('kommentar'));

$select = new Select('zufrieden', array(1 => 'Ja', 2 => 'Nein', 3 =>'geht so'), '');
$select->setLabel('sind Sie zufrieden?')->setRequired(true);
$form->add($select);

$plz = new InputText('plz', 'PLZ');
$plz->setPattern('^7207[0246]$', 'Dies ist keine gültige Tübinger PLZ', 'Geben Sie eine Postleitzahl aus dem Bereich 72070 bis 72076 ein', '7207x');

$form->add($plz);

$form->get('kommentar')->setAttribute('placeholder', 'Geben Sie hier Ihren Kommentar ein')
->setAttribute('rows', 6)->setAttribute('cols', 70);

$form->add(new InputCheckbox('agb', 'Ich stimme den AGB zu', 'ja'));
$form->get('agb')->setRequired(true, 'Sie müssen den AGB zustimmen');

$form->add(new InputRadioGroup('farbe', array('rot' => 'Rot', 'gruen' => 'Grün', 'gelb' => 'Gelb')));

$tage = array('mo' => 'Montag', 'di' => 'Dienstag', 'mi' => 'Mittwoch', 'do' => 'Donnerstag');


$form->add(InputCheckboxGroup::createInputCheckboxGroup('Wochentag', $tage)->setRequired(true, 'Bitte geben Sie mindestens einen Wochentag an'));

$form->add(Select::createSelect('monate', array('Januar', 'Februar', 'März', 'April'), 'bitte auswählen'));


$renderer = new FormRendererGeneric();
$renderer->setForm($form);

if (isset($_REQUEST['testform'])) {
    $form->bind($_REQUEST);
    if ($form->isValid()) {
        echo $renderer->renderResult();
        exit;
    }
}

echo $renderer->renderForm();



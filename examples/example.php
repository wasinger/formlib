<?php
namespace Wa72\Formlib;
if (file_exists(__DIR__ . "/../vendor/autoload.php")) require __DIR__ . "/../vendor/autoload.php";
else require __DIR__ . "/../../../../vendor/autoload.php";

//$transport = new \Swift_MailTransport();
$transport = new \Swift_SmtpTransport('your smtp server address');

$mailer = new \Swift_Mailer($transport);

$logger = new \Wa72SimpleLogger('/tmp/formlib.log'); // adjust to where you want to log to

$fh = new FormHandler($logger, $mailer);

$formname = 'testform';

$form = $fh->createFormFromYamlFile(__DIR__ . '/data/'.$formname.'.yml');

session_start();

if (isset($_POST[$formname]) && $fh->bindAndValidateForm($_POST)) { // data posted
    $result = $fh->processForm();
    if ($result) {
        $_SESSION['formlib_formhandler_result'] = '<p>Die folgenden Daten wurden übermittelt:</p>' . $fh->displaySubmittedData();
    } else {
        $_SESSION['formlib_formhandler_result'] = '<p>Ein Fehler trat auf. Ihre Daten konnten nicht übermittelt werden.</p>';
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
} elseif (isset($_SESSION['formlib_formhandler_result'])) { // data submitted, display result
    echo $_SESSION['formlib_formhandler_result'];
    unset($_SESSION['formlib_formhandler_result']);
} else { // display form
    echo $fh->displayForm();
}

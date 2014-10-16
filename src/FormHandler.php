<?php
namespace Wa72\Formlib;
use Symfony\Component\DomCrawler\Crawler;
use Wa72\Formlib\Field\InputCheckbox;
use Wa72\Formlib\Field\InputCheckboxGroup;
use Wa72\Formlib\Field\InputEmail;
use Wa72\Formlib\Field\InputHidden;
use Wa72\Formlib\Field\InputHiddenCsrfprotection;
use Wa72\Formlib\Field\InputNumber;
use Wa72\Formlib\Field\InputRadioGroup;
use Wa72\Formlib\Field\InputTel;
use Wa72\Formlib\Field\InputText;
use Wa72\Formlib\Field\InputUrl;
use Wa72\Formlib\Field\Select;
use Wa72\Formlib\Field\Textarea;

/**
 * FormHandler handles a form through its complete lifecycle:
 * create a form, display it, process data
 */
class FormHandler
{
    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $logger;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    protected $is_valid = false;
    protected $is_processed = false;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var FormprocessorInterface[]
     */
    protected $processors = array();

    /**
     * @var FormRendererInterface
     */
    protected $renderer;

    public function __construct(\Psr\Log\LoggerInterface $logger = null, \Swift_Mailer $mailer = null)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    /**
     * Creates and returns a new, empty form object.
     * Fields must still be added.
     * A reference is kept in $this->form.
     *
     * @param $name
     * @return \Wa72\Formlib\Form
     *
     * @see createFormFromYamlFile()
     * @see createFormFromArray()
     */
    public function createForm($name)
    {
        $this->form = new Form($name);
        if (empty($this->renderer)) $this->renderer = new FormRendererGeneric();
        $this->renderer->setForm($this->form);
        return $this->form;
    }

    public function registerProcessor(FormprocessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @return FormRendererGeneric
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setRenderer(FormRendererInterface $renderer)
    {
        $this->renderer = $renderer;
        if ($this->form) $this->renderer->setForm($this->form);
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get the HTML source code of the rendered form
     *
     * @throws \Exception
     * @return string HTML code of the rendered form
     */
    public function displayForm()
    {
        if (!($this->renderer instanceof FormRendererReturningRenderedFormInterface)) throw new \Exception('No FormRenderer implementing renderForm() set in FormHandler');
        return $this->renderer->renderForm();
    }


    /**
     * Bind data from an array (e.g. $_POST) to the form and check whether the form is valid
     *
     * @param array $data
     * @return bool
     */
    public function bindAndValidateForm(array $data)
    {
        $this->form->bind($data);
        $this->is_valid = $this->form->isValid();
        return $this->is_valid;
    }

    /**
     * Process the form using Formprocessors registered by registerProcessor()
     *
     * @return bool True if all processors succeed, false if at least one fails
     * @throws \LogicException
     */
    public function processForm()
    {
        if (!($this->is_valid)) throw new \LogicException('Form must be valid before processing');
        $this->logger->info('Form {form} on page {url} submitted from IP address {ip} using {ua}',
            array(
                'form' => $this->form->getName(),
                'url' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            )
        );
        if (!count($this->processors)) throw new \LogicException('No Formprocessors registered');
        $failure = false;
        foreach ($this->processors as $processor) {
            $processor->setLogger($this->logger);
            $result = $processor->processForm($this->form);
            if (!$result) $failure = true;
        }
        $this->is_processed = true;
        return !($failure);
    }

    /**
     * Display the filled out form
     *
     * @return string
     * @throws \LogicException
     * @throws \Exception
     */
    public function displaySubmittedData()
    {
        if (!($this->renderer instanceof FormRendererReturningRenderedFormInterface)) throw new \Exception('No FormRenderer implementing renderResult() set in FormHandler');
        if (!($this->is_processed)) throw new \LogicException('processForm() must be called before displaySubmittedData()');
        return $this->renderer->renderResult();
    }

    /**
     * Create a fully configured form from a configuration array
     *
     * @param string $name
     * @param array $configarray
     * @throws \InvalidArgumentException
     * @return Form
     */
    public function createFormFromArray($name, $configarray)
    {
        $form = $this->createForm($name);
        if (!isset($configarray['elements'])) throw new \InvalidArgumentException('createFormFromArray: configarray needs a key "elements"');
        foreach ($configarray['elements'] as $elname => $element) {
            $label = isset($element['label']) ? $element['label'] : (isset($element['options']['label']) ? $element['options']['label'] : ucfirst($elname));
            $type = (isset($element['type']) ? $element['type'] : 'text');
            $value = (isset($element['value']) ? $element['value'] : '');
            switch ($type) {
                case 'MultiCheckbox':
                case 'CheckboxGroup':
                case 'checkboxgroup':
                    $choices = (isset($element['options']['multioptions']) ? $element['options']['multioptions'] : $element['choices']);
                    $field = new InputCheckboxGroup($elname, $choices, $label);
                    break;
                case 'radio':
                case 'RadioGroup':
                case 'radiogroup':
                    $choices = (isset($element['options']['multioptions']) ? $element['options']['multioptions'] : $element['choices']);
                    $field = new InputRadioGroup($elname, $choices, $label, $value);
                    break;
                case 'select':
                    $choices = (isset($element['options']['multioptions']) ? $element['options']['multioptions'] : $element['choices']);
                    $add_empty_choice = (isset($element['add_empty_choice']) ? $element['add_empty_choice'] : null);
                    $field = new Select($elname, $choices, $add_empty_choice, $label, $value);
                    break;
                case 'checkbox':
                    $field = new InputCheckbox($elname, $label, $value);
                    if (isset($element['options']['unchecked_value'])) $field->setUncheckedValueForHumans($element['options']['unchecked_value']);
                    break;
                case 'textarea':
                    $field = new Textarea($elname, $label, $value);
                    if (isset($element['options']['rows'])) $field->setAttribute('rows', $element['options']['rows']);
                    if (isset($element['options']['cols'])) $field->setAttribute('cols', $element['options']['cols']);
                    break;
                case 'hidden':
                    $field = new InputHidden($elname, $value);
                    break;
                case 'email':
                    $field = new InputEmail($elname, $label, $value, (isset($element['errormessage']) ? $element['errormessage'] : ''));
                    if (isset($element['options']['size'])) $field->setAttribute('size', $element['options']['size']);
                    break;
                case 'tel':
                    $field = new InputTel($elname, $label, $value);
                    if (isset($element['options']['size'])) $field->setAttribute('size', $element['options']['size']);
                    break;
                case 'number':
                    $field = new InputNumber($elname, $label, $value);
                    if (isset($element['options']['size'])) $field->setAttribute('size', $element['options']['size']);
                    break;
                case 'url':
                    $field = new InputUrl($elname, $label, $value);
                    if (isset($element['options']['size'])) $field->setAttribute('size', $element['options']['size']);
                    break;
                case 'text':
                default:
                    $field = new InputText($elname, $label, $value);
                    if (isset($element['options']['size'])) $field->setAttribute('size', $element['options']['size']);
            }
            $required = isset($element['required']) ? $element['required'] : (isset($element['options']['required']) ? $element['options']['required'] : false);
            if (isset($element['attributes'])) {
                $field->setAttributes($element['attributes']);
            }
            if ($required) $field->setRequired(true);
            $form->add($field);
        }

        // TODO: make CSRF protection configurable
        $form->add(new InputHiddenCsrfprotection());

        if (isset($configarray['mail'])) $mc = $configarray['mail'];
        elseif (isset($configarray['options']['mail'])) $mc = $configarray['options']['mail'];
        if (isset($mc)) $this->registerProcessor(new FormprocessorSwiftmailer(FormprocessorMailConfiguration::fromArray($mc), $this->mailer));

        if (isset($configarray['options']['text_pre'])) {
            $this->renderer->content_before_form = $configarray['options']['text_pre'];
        }

        if (isset($configarray['templatefile'])) {
            $this->setRenderer(new FormRendererFullformTemplate(file_get_contents($configarray['templatefile'])));
        }

        return $form;
    }

    /**
     * Create a fully configured form from a configuration file in YAML format
     *
     * @param string $ymlfile Filename of a yaml file containing form configuration
     * @throws \RuntimeException if package symfony/yaml is not available
     * @throws \InvalidArgumentException if yaml file does not exist or is not readable
     * @throws \Symfony\Component\Yaml\Exception\ParseException if yaml file cannot be parsed
     * @return Form
     */
    public function createFormFromYamlFile($ymlfile)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) throw new \RuntimeException('package symfony/yaml not installed');
        if (!file_exists($ymlfile)) throw new \InvalidArgumentException('Form configuration file ' . $ymlfile . ' does not exist');
        if (!is_readable($ymlfile)) throw new \InvalidArgumentException('Form configuration file ' . $ymlfile . ' is not readable');
        $configarray = \Symfony\Component\Yaml\Yaml::parse($ymlfile);
        if (isset($configarray['form'])) $configarray = $configarray['form'];
        $name = (isset($configarray['id']) ? $configarray['id'] : (isset($configarray['name']) ? $configarray['name'] : basename($ymlfile, '.yml')));

        if (isset($configarray['templatefile'])) {
            // if config option "templatefile" given, treat it as a file name in the same directory as $ymlfile
            $configarray['templatefile'] = dirname($ymlfile) . '/' . $configarray['templatefile'];
        } else {
            // check for template: look for a file with the same name as $ymlfile but ending in .html
            $templatefile = preg_replace('/\.yml$/i', '.html', $ymlfile);
            if (file_exists($templatefile)) $configarray['templatefile'] = $templatefile;
        }
        return $this->createFormFromArray($name, $configarray);
    }

    public function createFromHtml($html)
    {
        $c = new Crawler($html);
        $configarray = array();
        $form = $c->selectButton($label_of_submitbutton)->form();
        $formfields = $form->all();
        foreach ($formfields as $formfield) {
            if ($formfield instanceof \Symfony\Component\DomCrawler\Field\ChoiceFormField) {

            }
        }
    }

}

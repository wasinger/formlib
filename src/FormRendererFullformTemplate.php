<?php
namespace Wa72\Formlib;

use Wa72\Formlib\Field\Field;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Renders a Form object to an HtmlPageCrawler object
 *
 */
class FormRendererFullformTemplate implements FormRendererReturningRenderedFormInterface
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * An HTML template containing the full form.
     *
     * When rendering the form, input elements in this template are replaced by those of the form
     * (based on their id attribute)
     *
     * @var \Wa72\HtmlPageDom\HtmlPageCrawler
     */
    protected $fullformtemplate;

    /**
     * HTML content to be wrapped around all global form error messages
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $global_errors_wrap_all = '<ul class="form-errors">';
    /**
     * HTML content to be wrapped around a global form error message
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $global_errors_wrap = '<li>';
    /**
     * HTML content to be wrapped around all error messages of a field
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $errors_wrap_all = '<ul class="form-errors">';
    /**
     * HTML content to be wrapped around a field error message
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $errors_wrap = '<li>';

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    /**
     * Set an HTML template containing the full form.
     *
     * When rendering the form, input elements in this template are replaced by those of the form
     * (based on their id attribute)
     *
     * @param string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler $template
     */
    public function __construct($template)
    {
        $this->fullformtemplate = HtmlPageCrawler::create($template);
    }

    /**
     * Render the form to a DOM tree
     *
     * @return HtmlPageCrawler
     */
    public function renderForm()
    {
        $t = $this->fullformtemplate;
        $fc = $t->filter('form');

        // global error messages are rendered as first child element of form.
        // TODO: make this configurable and themeable
        $em = $this->form->getErrorMessages();
        if (count($em)) {
            $fc->append($this->renderGlobalErrorMessages($em));
        }
        /**
         * @var Field[] $hiddenfields
         */
        $hiddenfields = array();
        foreach ($this->form->getFields() as $field) {
            if ($field->isHidden()) $hiddenfields[] = $field; // omit hidden fields but remember them
            $widget = HtmlPageCrawler::create($field->getWidget());
            $id = $widget->getAttribute('id');
            $wt = $t->filterXPath('descendant-or-self::*[@id = \'' . $id . '\']');
            if (count($wt)) {
                $wt->replaceWith($widget);
                $errors = $field->getErrorMessages();
                if (count($errors)) {
                    $errors = $this->renderFieldErrorMessages($errors);
                    $widget->before($errors);
                }
            } else { // element with full id (formname_fieldname) not found in template, try short id (fieldname)
                $id = $field->getName();
                $wt = $t->filterXPath('descendant-or-self::*[@id = \'' . $id . '\']');
                if (count($wt)) {
                    $wt->replaceWith($widget);
                    $errors = $field->getErrorMessages();
                    if (count($errors)) {
                        $errors = $this->renderFieldErrorMessages($errors);
                        $widget->before($errors);
                    }
                }
            }
        }
        // remove hidden fields from template
        $t->filter('input[type=hidden]')->remove();
        foreach ($hiddenfields as $field) { // output hidden fields at the end of the form
            $fc->append($field->getWidget());
        }
        return $t;
    }

    /**
     *
     *
     * @return HtmlPageCrawler
     */
    public function renderResult()
    {
        $t = $this->fullformtemplate;
        foreach ($this->form->getFields() as $field) {
            $widget = $field->getDataWidget(true);
            $widget = $t->getDOMDocument()->importNode($widget, true);
            $id = $widget->getAttribute('id');
            $wt = $t->filterXPath('descendant-or-self::*[@id = \'' . $id . '\']')->getNode(0);
            if ($wt instanceof \DOMElement) {
                $wt->parentNode->replaceChild($widget, $wt);
            } else { // element with full id (formname_fieldname) not found in template, try short id (fieldname)
                $id = $field->getName();
                $wt = $t->filterXPath('descendant-or-self::*[@id = \'' . $id . '\']')->getNode(0);
                if ($wt instanceof \DOMElement) {
                    $wt->parentNode->replaceChild($widget, $wt);
                }
            }
        }
        $t->filter('[type=submit]')->remove();
        $t->filter('[type=reset]')->remove();
        $t->filter('form')->removeAttribute('action');
        return $t;
    }

    /**
     * Render the input widget of a form field
     *
     * @param Field $field
     * @return HtmlPageCrawler
     */
    public function renderWidget(Field $field)
    {
        return HtmlPageCrawler::create($field->getWidget());
    }


    /**
     * Render global error messages of a form
     *
     * @param array $messages
     * @return HtmlPageCrawler
     */
    public function renderGlobalErrorMessages(array $messages)
    {
        $e = new HtmlPageCrawler($this->global_errors_wrap_all ? : '<div>');
        foreach ($messages as $message) {
            $m = HtmlPageCrawler::create($message)->appendTo($e);
            if ($this->global_errors_wrap) $m->wrap($this->global_errors_wrap);
        }
        return $e;
    }

    /**
     * Render error messages of a form field
     *
     * @param array $messages
     * @return HtmlPageCrawler
     */
    public function renderFieldErrorMessages(array $messages)
    {
        $e = new HtmlPageCrawler($this->errors_wrap_all ? : '<div>');
        foreach ($messages as $message) {
            $m = HtmlPageCrawler::create($message)->appendTo($e);
            if ($this->errors_wrap) $m->wrap($this->errors_wrap);
        }
        return $e;
    }
}

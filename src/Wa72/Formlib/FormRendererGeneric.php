<?php
namespace Wa72\Formlib;

use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Renders a Form object to an HtmlPageCrawler object
 *
 */
class FormRendererGeneric implements FormRendererReturningRenderedFormInterface
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * The action attribute of the form tag
     *
     * @var string
     */
    public $form_action;

    /**
     * The method attribute of the form tag
     *
     * @var string
     */
    public $form_http_method = 'POST';

    /**
     * HTML Code for buttons to be added to the form
     *
     * This HTML code is appended as last child to the form element
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $buttons = '<input type="submit">';

    /**
     * HTML content to be wrapped around the whole form
     *
     * @var string|\DOMNode|\DOMNodeList|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $form_wrap;
    /**
     * HTML content to be wrapped around all fields in the form
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $form_fields_wrap;
    /**
     * HTML content to be prepended to the form
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $content_before_form;
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
    /**
     * HTML content to be wrapped around a form row
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $row_wrap = '<div class="form-row">';
    /**
     * HTML content to be wrapped around a field label
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $label_wrap;
    /**
     * HTML content to be wrapped around a field input widget
     *
     * @var string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public $widget_wrap;
    /**
     * The position of a field label relative to the input widget.
     * One of FormRenderer::POSITION_BEFORE_WIDGET, FormRenderer::POSITION_AFTER_WIDGET, FormRenderer::POSITION_AROUND_WIDGET
     *
     * @var int
     */
    public $label_position = self::POSITION_BEFORE_WIDGET;

    const POSITION_BEFORE_WIDGET = 1;
    const POSITION_AFTER_WIDGET = 2;
    const POSITION_AROUND_WIDGET = 3;

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    /**
     * HTML Code for buttons to be added to the form
     *
     * This HTML code is appended as last child to the form element
     *
     * @param string|\DOMElement|\Wa72\HtmlPageDom\HtmlPageCrawler
     */
    public function setButtons($content)
    {
        $this->buttons = $content;
    }

    /**
     * Render the form to a DOM tree
     *
     * @return HtmlPageCrawler
     */
    public function renderForm()
    {
        $form = HtmlPageCrawler::create('<form>');
        $em = $this->form->getErrorMessages();
        if (count($em)) {
            $form->append($this->renderGlobalErrorMessages($em));
        }

        /**
         * @var Field[] $hiddenfields
         */
        $hiddenfields = array();
        $fields = HtmlPageCrawler::create('<div>');
        foreach ($this->form->getFields() as $field) {
            if ($field->isHidden()) $hiddenfields[] = $field; // omit hidden fields but remember them
            else $fields->append($this->renderRow($field));
        }

        if ($this->form_fields_wrap) {
            $fields->wrapInner($this->form_fields_wrap);
        }
        $form->append($fields->children());

        foreach ($hiddenfields as $field) { // output hidden fields at the end of the form
            $form->append($this->renderWidget($field));
        }

        if (!empty($this->form_action)) $form->setAttribute('action', $this->form_action);
        if (!empty($this->form_http_method)) $form->setAttribute('method', $this->form_http_method);

        // add submit button
        if (!empty($this->buttons)) $form->append($this->buttons);

        if ($this->form_wrap) {
            $form->wrap($this->form_wrap);
        }
        if ($this->content_before_form) {
            $form->before($this->content_before_form);
        }
        return HtmlPageCrawler::create($form->getDOMDocument()->documentElement->childNodes);
    }

    /**
     *
     *
     * @return HtmlPageCrawler
     */
    public function renderResult()
    {
        $form = HtmlPageCrawler::create('<form>');
        $fields = HtmlPageCrawler::create('<div>');
        foreach ($this->form->getFields() as $field) {
            if (!$field->getIncludeInData()) continue;
            $fields->append($this->renderRow($field, true));
        }
        if ($this->form_fields_wrap) {
            $fields->wrapInner($this->form_fields_wrap);
        }
        $form->append($fields->children());
        if ($this->form_wrap) {
            $form->wrap($this->form_wrap);
        }
        return HtmlPageCrawler::create($form->getDOMDocument()->documentElement->childNodes);
    }

    /**
     * render one form "row", i.e. one field including label, input widget, and error messages
     *
     * @param Field $field
     * @param bool $show_data If true, don't display input widget but submitted data (for displaying the filled out form)
     * @return HtmlPageCrawler
     */
    public function renderRow(Field $field, $show_data = false)
    {
        $widget = HtmlPageCrawler::create($show_data ? $field->getDataWidget() : $field->getWidget());
        $label = $field->getLabelElement() ? HtmlPageCrawler::create($field->getLabelElement()) : null;
        $errors = $field->getErrorMessages();
        if (count($errors)) {
            $errors = $this->renderFieldErrorMessages($errors);
        }
        $row = HtmlPageCrawler::create('<div>')->append($widget);
        if ($label)  {
            if ($this->label_position == self::POSITION_BEFORE_WIDGET) $widget->before($label);
            elseif ($this->label_position == self::POSITION_AFTER_WIDGET) $widget->after($label);
            elseif ($this->label_position == self::POSITION_AROUND_WIDGET) $widget->wrap($label);
        }
        if ($this->widget_wrap) $widget->wrap($this->widget_wrap);
        if ($errors) $widget->before($errors);
        if ($label && $this->label_wrap) $label->wrap($this->label_wrap);
        if ($this->row_wrap) $row->wrapInner($this->row_wrap);
        return $row->children();
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

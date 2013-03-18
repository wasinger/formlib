<?php
namespace Wa72\Formlib;

interface FormRendererInterface
{
    /*
     * The only requirement to a FormRenderer is that it can be given a Form object.
     *
     * There are no requirements about the presence of certain rendering functions
     * because these may vary according to the rendering backend used.
     *
     * If you want a renderer that can return a rendered HTML form using renderForm()
     * use FormRendererReturningRenderedFormInterface.
     */
    public function setForm(Form $form);

}
<?php
namespace Wa72\Formlib;

interface FormRendererReturningRenderedFormInterface extends FormRendererInterface
{
    public function renderForm();
    public function renderResult();
}
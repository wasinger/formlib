<?php
namespace Wa72\Formlib;

/**
 * A Formprocessor processes the submitted form data, e.g. sends it by e-mail or writes to a database
 *
 */
interface FormprocessorInterface
{
    /**
     * @param Form $form
     * @return bool True on success, false on failure
     */
    public function processForm(Form $form);

    /**
     * Inject a Logger
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return FormprocessorInterface self-reference for chaining
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger);
}
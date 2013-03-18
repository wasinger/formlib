<?php
namespace Wa72\Formlib;

class ErrorMessages
{
    static $required = 'This field is required';
    static $pattern = 'The value you have entered does not match the required format';
    static $maxlength = 'You can not enter more than %d characters.';
    static $email = 'Please enter a valid e-mail address';
    static $wrong_select_value = '%s is not a valid value for this field';
    static $csrf_not_valid = 'An error occured (CSRF token not valid). Please try to submit the form again.';
    static $csrf_too_fast = 'You submitted the form very quickly. Please review your input carefully and submit again.';
    static $form_has_errors = 'Some fields contain errors.';
}

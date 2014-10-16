<?php
namespace Wa72\Formlib;

class ValidatorPattern implements ValidatorInterface
{
    protected $errormessage;
    protected $pattern;
    protected $pass_empty;

    public function __construct($pattern, $errormessage = '', $pass_empty = true)
    {
        if ($errormessage) $this->errormessage = $errormessage;
        else $this->errormessage = ErrorMessages::$pattern;
        $this->pattern = '/' . $pattern . '/';
        $this->pass_empty = $pass_empty;
    }

    public function validate($value)
    {
        if (!$value && $this->pass_empty) return true;
        return preg_match($this->pattern, $value);
    }

    public function getErrorMessage()
    {
        return $this->errormessage;
    }
}
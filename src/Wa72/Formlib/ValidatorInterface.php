<?php
namespace Wa72\Formlib;

interface ValidatorInterface
{


    const STATUS_NOT_VALIDATED = 0;
    const STATUS_VALID = 1;
    const STATUS_INVALID = -1;

    /**
     * @param mixed $value
     * @return boolean
     */
    public function validate($value);

    public function getErrorMessage();
}
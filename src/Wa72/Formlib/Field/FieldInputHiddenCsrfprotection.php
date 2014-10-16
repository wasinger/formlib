<?php
namespace Wa72\Formlib\Field;
use Wa72\Formlib\ErrorMessages;
use Wa72\Formlib\ValidatorInterface;

/**
 * This class represents a hidden field that combines a CSRF protection with a time-based spam protection
 */
class FieldInputHiddenCsrfprotection extends FieldInputHidden
{
    const TIMEFRAME_VALID = 0;
    const TIMEFRAME_TOO_YOUNG = -1;
    const TIMEFRAME_TOO_OLD = 1;
    const TIMEFRAME_NOT_SET = 2;

    public $timeframe_min = 10;
    public $timeframe_max = 1200;

    public $errormessage_not_valid;
    public $errormessage_to_fast;

    public function __construct()
    {
        parent::__construct('csrfprotect');
        $this->errormessage_not_valid = ErrorMessages::$csrf_not_valid;
        $this->errormessage_to_fast = ErrorMessages::$csrf_too_fast;
    }

    public function getWidget()
    {
        $this->value = $this->getToken();
        return parent::getWidget();
    }

    public function validate()
    {
        if ($this->value && $this->value == $this->getTokenFromSession()) {
            $tf = $this->checkTimeframe();
            if ($tf == self::TIMEFRAME_TOO_YOUNG) {
                $this->form->addErrorMessage($this->errormessage_to_fast);
                $this->validation_status = ValidatorInterface::STATUS_INVALID;
            } elseif ($tf == self::TIMEFRAME_TOO_OLD || $tf == self::TIMEFRAME_NOT_SET) {
                $this->form->addErrorMessage($this->errormessage_not_valid);
                $this->validation_status = ValidatorInterface::STATUS_INVALID;
            } else {
                $this->validation_status = ValidatorInterface::STATUS_VALID;
            }
        } else {
            $this->form->addErrorMessage($this->errormessage_not_valid);
            $this->validation_status = ValidatorInterface::STATUS_INVALID;
        }
    }

    protected function getToken()
    {
        $token = $this->getTokenFromSession();
        if ($token && $this->checkTimeframe() == self::TIMEFRAME_VALID) return $token;
        else {
            $token = $this->generateToken();
            $this->saveTokenToSession($token);
            return $token;
        }
    }
    protected function generateToken()
    {
        return md5($_SERVER['REMOTE_ADDR'] . $this->form->getName() . uniqid('', true));
    }
    protected function saveTokenToSession($token)
    {
        $_SESSION['wa72formlib_csrf_token'] = $token;
        $_SESSION['wa72formlib_csrf_timestamp'] = time();
    }
    protected function getTokenFromSession()
    {
        return (isset($_SESSION['wa72formlib_csrf_token']) ? $_SESSION['wa72formlib_csrf_token'] : null);
    }

    /**
     * @return int one of the class constants TIMEFRAME_NOT_SET, TIMEFRAME_TOO_YOUNG, TIMEFRAME_TOO_OLD, TIMEFRAME_VALID
     */
    protected function checkTimeframe()
    {
        if (!isset($_SESSION['wa72formlib_csrf_timestamp'])) return static::TIMEFRAME_NOT_SET;
        $ts = $_SESSION['wa72formlib_csrf_timestamp'];
        if ((time() - $ts) < $this->timeframe_min) return static::TIMEFRAME_TOO_YOUNG;
        elseif ((time() - $ts) > $this->timeframe_max) return static::TIMEFRAME_TOO_OLD;
        else return static::TIMEFRAME_VALID;
    }
}

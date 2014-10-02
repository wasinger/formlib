<?php
namespace Wa72\Formlib;

class FormprocessorMailConfiguration
{
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $subject;
    /**
     * Whether the sender should recieve a copy of the mail
     *
     * @var boolean
     */
    public $copy_to_sender;

    /**
     * The name of the form field containing the sender's e-mail address
     * @var string
     */
    public $senderfield;

    public $text_pre;
    public $text_post;
    public $text_pre_sender;
    public $text_post_sender;

    private $required = array('to', 'subject');

    /**
     * @param array $array
     * @return FormprocessorMailConfiguration
     */
    static function fromArray($array)
    {
        $o = new static();
        foreach ($o as $name => $value) {
            if (isset($array[$name])) $o->$name = $array[$name];
        }
        $o->validate();
        return $o;
    }

    /**
     * @throws \Exception
     */
    public function validate()
    {
        foreach ($this->required as $field) {
            if (!$this->$field) throw new \Exception('Mailconfiguration: required field "' . $field . '" not set.');
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function set($name, $value)
    {
        if (!isset($this->$name)) throw new \InvalidArgumentException('Mailconfiguration: Property ' . $name . ' does not exist');
        $this->$name = $value;
        $this->validate();
    }
}

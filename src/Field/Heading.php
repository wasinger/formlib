<?php


namespace Wa72\Formlib\Field;


class Heading extends Field
{
    protected $name;
    private $text;
    private $tag;

    public function __construct($name, $text, $tag = 'h2')
    {
        $this->name = $name;
        $this->text = $text;
        $this->tag = $tag;
    }

    /**
     * Return the HTML DOM Element of the form input widget
     *
     * @return \DOMElement
     * @api
     */
    function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement($this->tag);
        $domel->nodeValue = $this->text;
        return $domel;
    }

    function getDataWidget($for_humans = true)
    {
        return $this->getWidget();
    }

    /**
     * Return the value of the field for further processing
     * Useful for printing the already filled out form
     *
     * @param bool $for_humans if true, return a data representation intended to be read by humans, if available
     * @return string
     * @api
     */
    function getData($for_humans = false)
    {
        return $this->text;
    }

    function getLabel() {
        return '';
    }
}
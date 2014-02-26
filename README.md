FORMLIB
=======

Yet another PHP library for creating and processing HTML forms (WIP).


## How it's different?

- It is intended for quick, configuration driven creation of contact forms that are sent by email. 
  It is not intended for creating editing forms for database records.

- Forms can either be defined by YAML configuration files (or by associative PHP arrays or any other format
  that can be turned into a PHP array) or programmatically using a fluent interface.

- Forms are rendered either in a customizable generic way or by replacing the form elements in an
  HTML template containing the whole form.

- Forms are processed using FormProcessors. Currently there is only a FormProcessor that sends the form by email
  using SwiftMailer but you can easily add your own form processor.

- Form elements are created using DOM and are returned as DOM objects. This way, Formlib does not depend on a particular
  templating system but it is possible to alter the form and its elements after creation by manipulation the DOM,
  e.g. by adding and altering attributes.

## Dependencies

- [symfony/yaml](https://github.com/symfony/yaml)

- SwiftMailer

- [HtmlPageDom](https://github.com/wasinger/htmlpagedom) for DOM manipulation which in turn builds upon
  [Symfony's DomCrawler](https://github.com/symfony/DomCrawler) component

## Installation

Formlib is registered on packagist.org so it can be installed using composer:

 require: "wa72/formlib": "dev-master"

## Usage

See the "examples" directory.

 

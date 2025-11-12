<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

require __DIR__ . '/../vendor/autoload.php';

use Liquid\Liquid;
use Liquid\Template;

/**
 * Creates a form, ala Shopify
 *
 * Example:
 *
 * {% form 'action' %}
 * <!-- form fields -->
 * {% endform %}
 *
 */

class TagForm extends AbstractBlock
{
    /**
     * The name of the form to assign to
     *
     * @var string
     */
    private $formName;


    /**
     * Constructor
     *
     * @param string $markup
     * @param Array $tokens
     * @param LiquidFileSystem $fileSystem
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        parent::__construct($markup, $tokens, $fileSystem);

        $syntax = new Regexp('/(contact|search|password)/');
        if ($syntax->match($markup)) {
            $this->formName = $syntax->matches[1];
            if ($this->formName == 'password') {
                $this->formName = ltrim($_SERVER['REQUEST_URI'], '/');
            }
        } else {
            throw new LiquidException("Syntax Error in 'form' - Valid syntax: form [contact|submission|password]");
        }
    }


    /**
     * Renders the block
     *
     * @param Context $context
     */
    public function render(Context $context)
    {
        $formAction = sprintf('/%s', $this->formName);
        if ( count($_GET) ) {
            $formAction .= sprintf('?%s', http_build_query($_GET));
        }

        $html = '<form action="%s" method="post" class="%s-form" id="%s-form" accept-charset="utf-8">%s</form>';

        $form = sprintf($html, $formAction, $this->formName, $this->formName, parent::render($context));

        return $form;
    }
}

$liquid = new Template();
$liquid->registerTag('form', TagForm::class);

$template = `
    <h1>Form Example</h1>
    {% form 'contact' %}
        <input type="text" name="name" placeholder="Your Name">
        <input type="email" name="email" placeholder="Your Email">
        <textarea name="message" placeholder="Please enter a message"></textarea>
        <button type="submit">Submit</button>
    {% endform %}
`;

$liquid->parse($template);

echo $liquid->render(array('hello' => 'hello world', 'goback' => '<a href=".">index</a>'));

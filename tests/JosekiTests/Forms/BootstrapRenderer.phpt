<?php

/**
 * Test:  Joseki\Forms\BootstrapRenderer.
 */

namespace JosekiTests\Forms;

use Joseki;
use Joseki\Forms\Form;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class Translator implements \Nette\Localization\ITranslator
{
    function translate($message, $count = null)
    {
        return strtoupper($message);
    }
}

class BootstrapRendererTest extends Tester\TestCase
{
    public function testUploadTranslate()
    {
        $renderer = new Joseki\Forms\BootstrapRenderer();

        $form = new Form;
        $form->setTranslator(new Translator);
        $form->setRenderer($renderer);
        $form->addUpload('file');
        Assert::matchFile(
            __DIR__ . '/data/BootstrapRenderer.upload1.expected',
            (string)$form
        );

        Joseki\Forms\Messages::$messages[Form::FIND_FILE] = 'aaa';
        Joseki\Forms\Messages::$messages[Form::CHANGE_FILE] = 'bbb';
        Joseki\Forms\Messages::$messages[Form::REMOVE_FILE] = 'ccc';

        Assert::matchFile(
            __DIR__ . '/data/BootstrapRenderer.upload2.expected',
            (string)$form
        );
    }

}

\run(new BootstrapRendererTest());

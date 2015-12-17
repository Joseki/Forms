<?php

/**
 * Test:  Joseki\Forms\Extension.
 */

namespace JosekiTests\Forms;

use Joseki;
use Joseki\Forms\Form;
use Nette;
use Nette\DI\Compiler;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class ExtensionTest extends Tester\TestCase
{

    private function prepareConfigurator()
    {
        $configurator = new Nette\Configurator();
        $configurator->setTempDirectory(TEMP_DIR);
        $configurator->addParameters(array('container' => array('class' => 'SystemContainer_' . Nette\Utils\Random::generate())));
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('joseki.forms', new  Joseki\Forms\DI\FormsExtension());
        };
        $configurator->addConfig(__DIR__ . '/data/config.extension.neon', Nette\Configurator::NONE);
        $configurator->defaultExtensions = array(
            'php' => 'Nette\DI\Extensions\PhpExtension',
            'constants' => 'Nette\DI\Extensions\ConstantsExtension',
            'extensions' => 'Nette\DI\Extensions\ExtensionsExtension',
            'decorator' => 'Nette\DI\Extensions\DecoratorExtension',
            'cache' => array('Nette\Bridges\CacheDI\CacheExtension', array('%tempDir%')),
            'di' => array('Nette\DI\Extensions\DIExtension', array('%debugMode%')),
            'forms' => 'Nette\Bridges\FormsDI\FormsExtension',
            'tracy' => array('Tracy\Bridges\Nette\TracyExtension', array('%debugMode%')),
            'inject' => 'Nette\DI\Extensions\InjectExtension',
        );


        return $configurator;
    }



    public function testMessages()
    {
        $configurator = $this->prepareConfigurator();

        Assert::equal(
            Joseki\Forms\Messages::$messages,
            [
                Form::FIND_FILE => 'Browse',
                Form::CHANGE_FILE => 'Change',
                Form::REMOVE_FILE => 'Remove',
            ]
        );

        /** @var \Nette\DI\Container $container */
        $configurator->createContainer();


        Assert::equal(
            Joseki\Forms\Messages::$messages,
            [
                Form::FIND_FILE => 'Foo',
                Form::CHANGE_FILE => 'Bar',
                Form::REMOVE_FILE => 'FooBar',
            ]
        );
    }

}

\run(new ExtensionTest());

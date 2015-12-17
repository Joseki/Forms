<?php

namespace Joseki\Forms\DI;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Validators;

class FormsExtension extends CompilerExtension
{

    public $defaults = [
        'messages' => [],
    ];



    public function afterCompile(ClassType $class)
    {
        $initialize = $class->getMethod('initialize');
        $config = $this->getConfig($this->defaults);
        Validators::assert($config['messages'], 'array');

        foreach ($config['messages'] as $key => $message) {
            if (!($name = constant($key))) {
                throw new \InvalidArgumentException("Forms message key '$key' constant not defined");
            }
            $initialize->addBody('\Joseki\Forms\Messages::$messages[?] = ?;', array($name, $message));
        }
    }

}

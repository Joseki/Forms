<?php

namespace Joseki\Forms\DI;

use Joseki\Forms\Messages;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

class FormsExtension extends CompilerExtension
{

    public $defaults = [
        'messages' => [],
    ];



    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);
        Validators::assert($config['messages'], 'array');

        foreach ($config['messages'] as $key => $message) {
            if (!($name = constant($key))) {
                throw new \InvalidArgumentException("Forms message key '$key' constant not defined");
            }
            Messages::$messages[$name] = $message;
        }
    }

}

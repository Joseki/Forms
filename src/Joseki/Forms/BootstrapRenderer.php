<?php

namespace Joseki\Forms;

use Nette;
use Nette\Forms\Controls;
use Nette\Forms\Form as NForm;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Utils\Html;

class BootstrapRenderer extends DefaultFormRenderer
{

    protected $defaults = array(
        'label' => 4,
        'control' => 8,
        'container' => 8
    );

    /** @var bool */
    private $controlsInit = false;



    public function __construct($container = null, $label = null, $control = null, $well = 'well')
    {
        $this->wrappers['controls']['container'] = null;
        $this->wrappers['pair']['container'] = 'div class=form-group';
        $this->wrappers['pair']['.error'] = 'has-error';
        $this->wrappers['control']['description'] = 'span class=help-block-description';
        $this->wrappers['control']['errorcontainer'] = 'span class=help-block';

        $this->setFormContainerWidth($container ?: $this->defaults['container'], $well);
        $this->setLabelAndControlWidth(
            $label !== null ? $label : $this->defaults['label'],
            $control !== null ? $control : $this->defaults['control']
        );
    }



    public function setFormContainerWidth($width, $well)
    {
        if ($width > 12 || $width <= 0 || $width % 2 === 1) {
            throw new Nette\InvalidArgumentException;
        }
        $margin = (12 - $width) / 2;
        $this->wrappers['form']['container'] = 'div class="' . $well . ' form-container col-md-push-' . $margin . ' col-md-' . $width . '"';
        return $this;
    }



    public function setLabelAndControlWidth($label, $control)
    {
        if ($label + $control !== 12) {
            throw new Nette\InvalidStateException('label and control must give 12 in total');
        }
        $this->wrappers['control']['container'] = 'div class=col-sm-' . $control;
        $this->wrappers['label']['container'] = 'div class="col-sm-' . $label . ' control-label"';
        return $this;
    }



    public function renderBegin()
    {
        $this->controlsInit();
        return parent::renderBegin();
    }



    private function controlsInit()
    {
        if ($this->controlsInit) {
            return;
        }

        $this->controlsInit = true;
        $this->form->getElementPrototype()->addClass('form-horizontal');
        foreach ($this->form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                if (empty($usedPrimary) && $control->parent instanceof NForm) {
                    $class = 'btn btn-primary';
                    $usedPrimary = true;
                } else {
                    $class = 'btn btn-default';
                }
                $control->getControlPrototype()->addClass($class);

            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');

            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }

            if ($control instanceof Controls\SelectBox) {
                $control->getControlPrototype()->addClass('selectpicker');
            }
        }
    }



    public function renderEnd()
    {
        $this->controlsInit();
        return parent::renderEnd();
    }



    public function renderBody()
    {
        $this->controlsInit();
        return parent::renderBody();
    }



    public function renderControls($parent)
    {
        $this->controlsInit();
        return parent::renderControls($parent);
    }



    public function renderPair(Nette\Forms\IControl $control)
    {
        $this->controlsInit();
        return parent::renderPair($control);
    }



    public function renderPairMulti(array $controls)
    {
        $this->controlsInit();

        $s = array();
        $allButtons = true;
        foreach ($controls as $control) {
            if (!$control instanceof Nette\Forms\IControl) {
                throw new Nette\InvalidArgumentException('Argument must be array of IFormControl instances.');
            }
            $allButtons = $allButtons && $control instanceof Controls\SubmitButton;
            $description = $this->prepareDescription($control);
            $s[] = $control->getControl() . $description;
        }
        $pair = $this->getWrapper('pair container');
        $pair->add($this->renderLabel($control));

        $wrapper = $this->getWrapper('control container');
        if ($allButtons) {
            $wrapper->setHtml(Html::el('div class="btn-group pull-right"')->setHtml(implode(' ', array_reverse($s))));
        } else {
            $wrapper->setHtml(implode(' ', $s));
        }
        $pair->add($wrapper);
        return $pair->render(0);
    }



    private function prepareDescription($control)
    {
        $description = $control->getOption('description');
        if ($description instanceof Html) {
            $description = ' ' . $description;
        } elseif (is_string($description)) {
            $description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));
        } else {
            $description = '';
        }
        return $description;
    }



    public function renderLabel(Nette\Forms\IControl $control)
    {
        $this->controlsInit();
        return parent::renderLabel($control);
    }



    public function renderControl(Nette\Forms\IControl $control)
    {
        $this->controlsInit();
        if ($control instanceof Nette\Forms\Controls\UploadControl) {
            return $this->renderUploadControl($control);
        }
        return parent::renderControl($control);
    }



    private function renderUploadControl(Nette\Forms\Controls\UploadControl $control)
    {
        list($body, $description) = $this->prepareRenderControl($control);
        $c = $control->getControl();
        $class = isset($c->class, $c->class[0]) ? $c->class[0] : '';

        $skeleton = <<<HTML

        <div class="fileinput fileinput-new input-group %s" data-provides="fileinput">
            <div class="form-control" data-trigger="fileinput">
                <span class="fileinput-filename"></span>
            </div>
            <span class="input-group-addon btn btn-default btn-file">
                <span class="fileinput-new">%s</span>
                <span class="fileinput-exists">%s</span>
                %s
            </span>
            <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">%s</a>
        </div>
    %s%s
HTML;
        $html = sprintf(
            $skeleton,
            $class,
            $control->translate(Messages::$messages[Form::FIND_FILE]),
            $control->translate(Messages::$messages[Form::CHANGE_FILE]),
            $control->getControl(),
            $control->translate(Messages::$messages[Form::REMOVE_FILE]),
            $this->renderErrors($control),
            $description
        );

        return $body->setHtml($html);
    }



    private function prepareRenderControl(Nette\Forms\IControl $control)
    {
        $body = $this->getWrapper('control container');
        if ($this->counter % 2) {
            $body->class($this->getValue('control .odd'), true);
        }

        $description = $this->prepareDescription($control);

        if ($control->isRequired()) {
            $description = $this->getValue('control requiredsuffix') . $description;
        }

        return [$body, $description];
    }
}

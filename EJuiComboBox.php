<?php

/**
 * jQuery combobox Yii extension
 *
 * Allows selecting a value from a dropdown list or entering in text.
 * Also works as an autocomplete for items in the select.
 *
 * @copyright © Digitick <www.digitick.net> 2011
 * @license GNU Lesser General Public License v3.0
 * @author Ianaré Sévi
 * @author Jacques Basseck
 *
 */
Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * Base class.
 */
class EJuiComboBox extends CJuiInputWidget
{

    /**
     * @var array the entries that the autocomplete should choose from.
     */
    public $data = array();
    /**
     * @var bool whether the $data is an associative array or not
     */
    public $assoc = true;
    /**
     * @var string A jQuery selector used to apply the widget to the element(s).
     * Use this to have the elements keep their binding when the DOM is manipulated
     * by Javascript, ie ajax calls or cloning.
     * Can also be useful when there are several elements that share the same settings,
     * to cut down on the amount of JS injected into the HTML.
     */
    public $scriptSelector;
    public $defaultOptions = array('allowText' => true);
    private $_id;
    private $_name;

    protected function setSelector($id, $script, $event = null)
    {
        if ($this->scriptSelector) {
            if (!$event)
                $event = 'focusin';
            $js = "jQuery('body').delegate('{$this->scriptSelector}','{$event}',function(e){\$(this).{$script}});";
            $id = $this->scriptSelector;
        }
        else {
            $js = "jQuery('#{$id}').{$script}";
        }
        return array($id, $js);
    }

    public function init()
    {
        $cs = Yii::app()->getClientScript();
        $assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets');
        $cs->registerScriptFile($assets . '/jquery.ui.widget.min.js');
        $cs->registerScriptFile($assets . '/jquery.ui.combobox.js');

        parent::init();
    }

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run()
    {
        $this->_setInputNameAndId();
        $this->_prepareData();
        $this->_printDropDownList();
        $this->_printTextField();
        $this->options = array_merge($this->defaultOptions, $this->options);

        $encodedOptions = CJavaScript::encode($this->options);

        $cs = Yii::app()->getClientScript();

        list($scriptId, $js) = $this->setSelector(
            $this->_id, "combobox({$encodedOptions});"
        );
        $cs->registerScript(__CLASS__ . '#' . $scriptId, $js);
    }

    private function _setInputNameAndId()
    {
        list($this->_name, $this->_id) = $this->resolveNameID();
    }

    private function _prepareData()
    {
        if (is_array($this->data) && !empty($this->data)) {
//if $data is not an assoc array make each value its key
            $data = ($this->assoc) ?
                $this->data :
                array_combine($this->data, $this->data);
        } else {
            $data = array();
        }
        $this->data = $data;
    }

    private function _printDropDownList()
    {
        echo CHtml::dropDownList(
            null, null, $this->data, array('id' => $this->_id . '_select')
        );
    }

    private function _printTextField()
    {
        if ($this->hasModel()) {
            echo CHtml::activeTextField(
                $this->model, $this->attribute, $this->htmlOptions
            );
        } else {
            echo CHtml::textField($this->_name, $this->value, $this->htmlOptions);
        }
    }

}
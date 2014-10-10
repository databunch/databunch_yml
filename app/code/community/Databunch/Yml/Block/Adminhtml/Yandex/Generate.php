<?php
class Databunch_Yml_Block_Adminhtml_Yandex_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('yml/adminhtml_yandex/generate');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel($this->__('Run'))
            ->setOnClick("setLocation('$url')")
            ->toHtml();

        return $html;
    }
}
<?php
class Databunch_Yml_Block_Adminhtml_Yandex_Link extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $url = htmlspecialchars(Mage::getBaseUrl('media') . 'yml/yandex.yml');

        $html = "<label><a href='" . $url . "'>" . $url . "</a></label>";

        return $html;
    }
}
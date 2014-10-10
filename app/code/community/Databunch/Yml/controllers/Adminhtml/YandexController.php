<?php
class Databunch_Yml_Adminhtml_YandexController extends Mage_Adminhtml_Controller_Action
{
    public function generateAction()
    {
        Mage::getModel('yml/yml_yandex')->generate();
        $this->_redirect('adminhtml/system_config/edit/section/yml');
    }

}
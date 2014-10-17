<?php
class Databunch_Yml_Adminhtml_YandexController extends Mage_Adminhtml_Controller_Action
{
    public function generateAction()
    {
        try {
            Mage::getModel('yml/yml_yandex')->generate();
            $url = Mage::getBaseUrl('media') . 'yml/yandex.yml';
            $this->_getSession()->addSuccess($this->__('YML file has been generated and now available at %s', $url));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/system_config/edit/section/yml');
    }

}
<?php

class Databunch_Yml_Model_Yml_Yandex extends Databunch_Yml_Model_Yml
{
    protected function _construct()
    {
        parent::_construct();
        $this->_filename = 'yandex.yml';
    }

    protected function addHeaderTags()
    {
        $this->_io->streamWrite('<?xml version="1.0" encoding="utf-8"?>');
        $this->_io->streamWrite('<!DOCTYPE yml_catalog SYSTEM "shops.dtd">');
    }

    protected function addOpenTags()
    {
        $this->_io->streamWrite('<yml_catalog date="' . Mage::getModel('core/date')->date('Y-m-d H:i') . '">');
        $this->_io->streamWrite('<shop>');
    }

    protected function addCloseTags()
    {
        $this->_io->streamWrite('</shop>');
        $this->_io->streamWrite('</yml_catalog>');
    }

    protected function generateCron()
    {
        $enabled = Mage::getStoreConfig('yml/yandex/enabled');
        if ($enabled)
        {
            $this->generate();
        }
    }
}
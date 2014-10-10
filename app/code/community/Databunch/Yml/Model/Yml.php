<?php

class Databunch_Yml_Model_Yml extends Mage_Core_Model_Abstract
{
    const CATEGORY_LEVEL = 2;

    protected $_io;
    protected $_filename;
    protected $_filepath;
    protected $_baseUrl;
    protected $_baseMediaUrl;

    protected function _construct()
    {
        $this->_io = new Varien_Io_File();
        $this->_filepath = 'yml/';
    }

    protected function _getPath()
    {
        return Mage::getBaseDir('media') . '/' . $this->_filepath;
    }

    protected function _getFilename()
    {
        return $this->_filename;
    }

    protected function _open()
    {
        $this->_io->setAllowCreateFolders(true);
        $this->_io->open(array('path' => $this->_getPath()));

        if ($this->_io->fileExists($this->_getFilename()) && !$this->_io->isWriteable($this->_getFilename())) {
            Mage::throwException(Mage::helper('yml')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->_filename(), $this->_getPath()));
        }

        $this->_io->streamOpen($this->_getFilename());
    }

    protected function _close()
    {
        $this->_io->streamClose();
    }

    protected function addShopInfo()
    {
        $name = Mage::getStoreConfig('yml/shop_info/name');
        $company = Mage::getStoreConfig('yml/shop_info/company');
        $this->_baseUrl = Mage::app()->getStore(1)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $this->_baseMediaUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl(null);

        $this->_io->streamWrite(
            sprintf(
                '<name>%s</name><company>%s</company><url>%s</url>',
                htmlspecialchars($name),
                htmlspecialchars($company),
                htmlspecialchars($this->_baseUrl)
            )
        );
    }

    protected function addCurrencies()
    {
        $this->_io->streamWrite('<currencies><currency id="RUR" rate="1"/></currencies>');
    }


    protected function addCategories()
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', array('gteq' => self::CATEGORY_LEVEL))
            ->setOrder('entity_id', 'ASC');

        $this->_io->streamWrite('<categories>');

        foreach ($categories as $category)
        {
            if ($category->getLevel() == self::CATEGORY_LEVEL)
            {
                $this->_addFirstLevelCategory($category);
            }
            else
            {
                $this->_addChildCategory($category);
            }
        }

        $this->_io->streamWrite('</categories>');
    }

    protected function _addFirstLevelCategory($category)
    {
        $xml = sprintf(
            '<category id="%d">%s</category>',
            $category->getId(),
            htmlspecialchars($category->getName())
        );

        $this->_io->streamWrite($xml);
    }

    protected function _addChildCategory($category)
    {
        $xml = sprintf(
            '<category id="%d" parentId="%d">%s</category>',
            $category->getId(),
            $category->getParentId(),
            htmlspecialchars($category->getName())
        );

        $this->_io->streamWrite($xml);
    }

    protected function addLocalDeliveryCost()
    {
        $localDeliveryCost = Mage::getStoreConfig('yml/shop_info/local_delivery_cost');
        if ($localDeliveryCost)
        {
            $this->_io->streamWrite(
                sprintf(
                    '<local_delivery_cost>%s</local_delivery_cost>',
                    $localDeliveryCost
                )
            );
        }
    }

    protected function addProducts()
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToFilter('type_id', array('eq' => 'simple'))
            ->addPriceData(null, 1)
            ->setStoreId(1)
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addUrlRewrite()
            ->setOrder('entity_id', 'DESC')
            ->load();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $this->_io->streamWrite('<offers>');

        foreach ($products as $product)
        {
            $this->_addSimpleOffer($product);
        }
        $this->_io->streamWrite('</offers>');
    }

    protected function _addSimpleOffer($product)
    {
        $xml = sprintf(
            '<offer id="%d" available="true">',
            $product->getId()
        );

        $url = htmlspecialchars($this->_baseUrl . $product->getUrlKey() . '/');
        $price = $product->getFinalPrice();
        $xml .= sprintf(
            '<url>%s</url><price>%.2f</price><currencyId>RUR</currencyId>',
            $url,
            $price
        );

        $categoryId = 0;
        $categories = $product->getCategoryIds();
        if (is_array($categories))
        {
            $categoryId = $categories[0];
        }
        if ($categoryId)
        {
            $xml .= sprintf(
                '<categoryId>%d</categoryId>',
                $categoryId
            );
        }
        else
        {
            return;
        }

        $picture = htmlspecialchars($this->_baseMediaUrl . trim($product->getImage(),'/'));
        if ($picture)
        {
            $xml .= sprintf(
                '<picture>%s</picture>',
                $picture
            );
        }

        $name = htmlspecialchars($product->getName());
        if ($name)
        {
            $xml .= sprintf(
                '<name>%s</name>',
                $name
            );
        }

        $manufacturer = "";
        if ($product->getManufacturer())
        {
            $manufacturer = $product->getAttributeText('manufacturer');
        }
        if ($manufacturer)
        {
            $xml .= sprintf(
                '<vendor>%s</vendor>',
                $manufacturer
            );
        }

        $description = htmlspecialchars(html_entity_decode(strip_tags($product->getDescription())));
        if ($description)
        {
            $xml .= sprintf(
                '<description>%s</description>',
                $description
            );
        }

        $xml .= '</offer>';

        $this->_io->streamWrite($xml);
    }

    public function generate()
    {
        $this->_open();
        $this->addHeaderTags();
        $this->addOpenTags();
        $this->addShopInfo();
        $this->addCurrencies();
        $this->addCategories();
        $this->addLocalDeliveryCost();
        $this->addProducts();
        $this->addCloseTags();
        $this->_close();
    }
}
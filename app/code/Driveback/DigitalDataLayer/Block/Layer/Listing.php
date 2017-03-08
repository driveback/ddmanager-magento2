<?php

namespace Driveback\DigitalDataLayer\Block\Layer;

use Driveback\DigitalDataLayer\Block\Layer;
use Driveback\DigitalDataLayer\Model\DataType\Listing as ListingDataType;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Listing
 */
class Listing extends Template
{
    /**
     * @var ListingDataType
     */
    protected $_listingDataType;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Listing constructor.
     * @param Context $context
     * @param ListingDataType $listing
     * @param array $data
     */
    public function __construct(
        Context $context,
        ListingDataType $listing,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_listingDataType = $listing;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * @return bool
     */
    protected function _isEnabled()
    {
        $store = $this->_storeManager->getStore();
        return (bool)$this->_scopeConfig->getValue(Layer::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_listingDataType->setListName($this->getListName());
        $this->_listingDataType->setProductListBlockName($this->getProductListBlockName());
        $this->_listingDataType->setShowCategory($this->getShowCategory());
        return parent::_beforeToHtml();
    }

    /**
     * @return ListingDataType
     */
    public function getListingDataType()
    {
        return $this->_listingDataType;
    }
}

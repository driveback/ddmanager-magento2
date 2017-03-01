<?php

namespace Driveback\DigitalDataLayer\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Driveback\DigitalDataLayer\Model\DataType\Pool as DataTypePool;

/**
 * Class Layer
 */
class Layer extends Template
{
    const XML_PATH_ENABLED = 'driveback_ddl/settings/layer_enabled';

    /**
     * @var DataTypePool
     */
    protected $_dataTypePool;
    
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
     * Layer constructor.
     * @param Context $context
     * @param DataTypePool $dataTypePool
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataTypePool $dataTypePool,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataTypePool = $dataTypePool;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * @return bool
     */
    protected function _isEnabled()
    {
        $store = $this->_storeManager->getStore();
        return (bool) $this->_scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return array
     */
    public function getDigitalData()
    {
        $data = [];
        foreach ($this->_dataTypePool->getTypesInstances() as $dataType) {
            $key = $dataType->getDigitalDataKey();
            $value = $dataType->getDigitalDataValue();
            if ($value === null) {
                continue;
            }
            $data[$key] = $value;
        }
        return $data;
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
}

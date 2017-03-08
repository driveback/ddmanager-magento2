<?php

namespace Driveback\DigitalDataLayer\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Layer
 */
class Manager extends Template
{
    const XML_PATH_ENABLED = 'driveback_ddl/settings/manager_enabled';
    const XML_PATH_PROJECT_ID = 'driveback_ddl/settings/project_id';

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
     * Manager constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * @return bool
     */
    protected function _isEnabled()
    {
        $store = $this->_storeManager->getStore();
        return (bool)$this->_scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return array
     */
    public function getProjectId()
    {
        $store = $this->_storeManager->getStore();
        return $this->_scopeConfig->getValue(self::XML_PATH_PROJECT_ID, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (true || !$this->_isEnabled() || !$this->getProjectId()) {
            return '';
        }
        return parent::_toHtml();
    }
}

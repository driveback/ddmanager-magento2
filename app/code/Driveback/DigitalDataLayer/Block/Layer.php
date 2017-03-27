<?php

namespace Driveback\DigitalDataLayer\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Driveback\DigitalDataLayer\Model\DataType\Pool as DataTypePool;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

/**
 * Class Layer
 */
class Layer extends Template
{
    const XML_PATH_ENABLED = 'driveback_ddl/settings/layer_enabled';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var DataTypePool
     */
    protected $_dataTypePool;

    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * Layer constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DataTypePool $dataTypePool
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataTypePool $dataTypePool,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_dataTypePool = $dataTypePool;
        $this->_helper = $dataHelper;
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
        if (!$this->_helper->isLayerEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setRegistryValue($key, $value)
    {
        $this->_registry->unregister($key);
        $this->_registry->register($key, $value);
        return $this;
    }
}

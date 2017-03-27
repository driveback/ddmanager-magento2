<?php

namespace Driveback\DigitalDataLayer\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

/**
 * Class Layer
 */
class Manager extends Template
{
    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * Manager constructor.
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $dataHelper;
    }

    /**
     * @return array
     */
    public function getProjectId()
    {
        return $this->_helper->getProjectId();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_helper->isManagerEnabled() || !$this->getProjectId()) {
            return '';
        }
        return parent::_toHtml();
    }
}

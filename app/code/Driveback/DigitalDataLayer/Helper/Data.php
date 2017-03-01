<?php

namespace Driveback\DigitalDataLayer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Driveback\DigitalDataLayer\Model\PageType\Pool as PageTypePool;
use Driveback\DigitalDataLayer\Model\PageType\PageTypeInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * @var PageTypePool
     */
    protected $_pageTypePool;

    /**
     * @var PageTypeInterface
     */
    protected $_currentPageType;
    
    /**
     * Data constructor.
     * @param Context $context
     * @param PageTypePool $pageTypePool
     */
    public function __construct(
        Context $context,
        PageTypePool $pageTypePool
    ) {
        parent::__construct($context);
        $this->_pageTypePool = $pageTypePool;
    }

    /**
     * @return PageTypeInterface
     * @throws LocalizedException
     */
    public function getCurrentPageType()
    {
        if ($this->_currentPageType === null) {
            foreach ($this->_pageTypePool->getTypesInstances() as $pageType) {
                if ($pageType->isCurrentPageType()) {
                    $this->_currentPageType = $pageType;
                    break;
                }
            }
            if (!$this->_currentPageType) {
                throw new LocalizedException(__('Could not resolve current page type.'));
            }
        }
        
        return $this->_currentPageType;
    }
}

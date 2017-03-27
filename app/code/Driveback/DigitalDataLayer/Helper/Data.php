<?php

namespace Driveback\DigitalDataLayer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\ScopeInterface;
use Driveback\DigitalDataLayer\Model\PageType\Pool as PageTypePool;
use Driveback\DigitalDataLayer\Model\PageType\PageTypeInterface;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const XML_PATH_LAYER_ENABLED = 'driveback_ddl/settings/layer_enabled';
    const XML_PATH_MANAGER_ENABLED = 'driveback_ddl/settings/manager_enabled';
    const XML_PATH_PROJECT_ID = 'driveback_ddl/settings/project_id';

    const CART_PRODUCT_QUANTITIES = 'ddl_prev_product_qty';
    const COOKIE_ADD_TO_CART = 'ddl_add_to_cart';
    const COOKIE_REMOVE_FROM_CART = 'ddl_remove_from_cart';

    /**
     * @var PageTypePool
     */
    protected $_pageTypePool;

    /**
     * @var PageTypeInterface
     */
    protected $_currentPageType;

    /**
     * @var array
     */
    protected static $_categoryArrayById = [];

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
     * @param null $store
     * @return bool
     */
    public function isLayerEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LAYER_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isManagerEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_MANAGER_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getProjectId($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PROJECT_ID, ScopeInterface::SCOPE_STORE, $store);
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

    /**
     * @param Category $category
     * @return array|null
     */
    public function getCategoryArrayByCategory(Category $category)
    {
        /**
         * @var $parentCategory Category
         * @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
         */
        if (!isset(self::$_categoryArrayById[$category->getId()])) {
            if ($category->getLevel() <= 1) {
                self::$_categoryArrayById[$category->getId()] = null;
            } else {
                $categories = [];
                if ($category->getLevel() > 2) {
                    $categoryIds = $category->getPathIds();
                    array_pop($categoryIds);

                    $collection = $category->getCollection();
                    $collection->setStore($category->getStore());
                    $collection->addIdFilter($categoryIds);
                    $collection->addIsActiveFilter();
                    $collection->addNameToResult();
                    $collection->addAttributeToFilter('level', ['gt' => 1]);
                    $collection->addAttributeToSort('level', $collection::SORT_ORDER_ASC);

                    foreach ($collection as $parentCategory) {
                        $categories[] = $parentCategory->getName();
                    }
                }

                $categories[] = $category->getName();
                self::$_categoryArrayById[$category->getId()] = $categories;
            }
        }

        return self::$_categoryArrayById[$category->getId()];
    }
}

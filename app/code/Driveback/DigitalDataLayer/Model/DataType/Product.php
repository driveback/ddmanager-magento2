<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Product
 */
class Product implements DataTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var ProductHelper
     */
    protected $_productHelper;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $_stockRegistryProvider;

    /**
     * Review model
     *
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Product constructor.
     * @param RequestInterface $request
     * @param Registry $registry
     * @param ProductHelper $productHelper
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ReviewFactory $reviewFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        RequestInterface $request,
        Registry $registry,
        ProductHelper $productHelper,
        StockRegistryProviderInterface $stockRegistryProvider,
        ReviewFactory $reviewFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_productHelper = $productHelper;
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_reviewFactory = $reviewFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'product';
    }

    /**
     * @return bool
     */
    protected function _isProductPage()
    {
        return $this->_request->getRouteName() == 'catalog'
        && $this->_request->getControllerName() == 'product'
        && $this->_request->getActionName() == 'view';
    }

    /**
     * @return null|array
     */
    public function getDigitalDataValue()
    {
        if (!$this->_isProductPage()) {
            return null;
        }

        $product = $this->_getCurrentProduct();
        return $this->getDigitalDataValueByProduct($product);
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    protected function _getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * @see \Magento\CatalogInventory\Model\StockStateProvider::getStockQty
     * @param \Magento\Catalog\Model\Product $product
     * @param int $websiteId
     * @return float|int|null
     */
    protected function _getProductStockQty(\Magento\Catalog\Model\Product $product, $websiteId)
    {
        $stockItem = $this->_stockRegistryProvider->getStockItem($product->getId(), $websiteId);
        if (!$product->isComposite()) {
            $stockQty = $stockItem->getQty();
        } else {
            $stockQty = null;
            $productsByGroups = $product->getTypeInstance()->getProductsToPurchaseByReqGroups($product);
            foreach ($productsByGroups as $productsInGroup) {
                $qty = 0;
                foreach ($productsInGroup as $childProduct) {
                    $qty += $this->_getProductStockQty($childProduct, $websiteId);
                }
                if (null === $stockQty || $qty < $stockQty) {
                    $stockQty = $qty;
                }
            }
        }
        $stockQty = (float)$stockQty;
        if ($stockQty < 0 || !$stockItem->getManageStock() || !$stockItem->getIsInStock()
            || !$product->isSaleable()
        ) {
            $stockQty = 0;
        }

        return $stockQty;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function _loadNotLoadedProductAttributes(\Magento\Catalog\Model\Product $product)
    {
        $attributes = [
            'image',
            'thumbnail',
            'size',
            'color'
        ];
        $resource = $product->getResource();
        foreach ($attributes as $attrCode) {
            if (!$resource->getAttribute($attrCode)) {
                continue;
            }
            if ($product->hasData($attrCode)) {
                continue;
            }
            $value = $resource->getAttributeRawValue($product->getId(), $attrCode, $product->getStoreId());
            $product->setData($attrCode, $value);
        }

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function _getProductCategories(\Magento\Catalog\Model\Product $product)
    {
        /**
         * @var $category \Magento\Catalog\Model\Category
         */
        $defaultValue = null;
        $categoryIds = $product->getAvailableInCategories();
        if (!$categoryIds) {
            return $defaultValue;
        }

        $collection = $this->_categoryCollectionFactory->create();
        $collection->setStore($product->getStore());
        $collection->addIdFilter($categoryIds);
        $collection->addIsActiveFilter();
        $collection->addAttributeToFilter('level', ['gt' => 1]);
        $collection->addAttributeToSort('level', $collection::SORT_ORDER_DESC);
        $collection->addAttributeToSort('position', $collection::SORT_ORDER_ASC);
        $collection->setPageSize(1);
        if (!count($collection)) {
            return $defaultValue;
        }

        $category = $collection->getFirstItem();
        $categoryIds = $category->getPathIds();

        $collection = $this->_categoryCollectionFactory->create();
        $collection->setStore($product->getStore());
        $collection->addIdFilter($categoryIds);
        $collection->addNameToResult();
        $collection->addAttributeToFilter('level', ['gt' => 1]);
        $collection->addAttributeToSort('level', $collection::SORT_ORDER_ASC);
        if (!count($collection)) {
            return $defaultValue;
        }

        $result = [];
        foreach ($collection as $category) {
            $result[] = $category->getName();
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return null
     */
    protected function _getRatingSummary(\Magento\Catalog\Model\Product $product)
    {
        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $product->getStoreId());
        }
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        if ($ratingSummary > 0) {
            return round($ratingSummary / 100 * 5, 2);
        }
        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getDigitalDataValueByProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_loadNotLoadedProductAttributes($product);

        $data = [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'currency' => $product->getStore()->getCurrentCurrencyCode(),
            'unitPrice' => round($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), 2),
            'unitSalePrice' => round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), 2),
            'isTaxIncluded' => $product->getPriceInfo()->getAdjustment('tax')->isIncludedInDisplayPrice(),
        ];

        $category = $this->_getProductCategories($product);
        if ($category !== null) {
            $data['category'] = $category;
        }

        $ratingSummary = $this->_getRatingSummary($product);
        if ($ratingSummary !== null) {
            $data['rating'] = $ratingSummary;
        }

        $data['url'] = $product->getProductUrl();

        $resource = $product->getResource();

        if ($product->getImage()) {
            $data['imageUrl'] = $resource->getAttribute('image')->getFrontend()->getUrl($product);
        }
        if ($product->getThumbnail()) {
            $data['thumbnailUrl'] = $resource->getAttribute('thumbnail')->getFrontend()->getUrl($product);
        }

        $attributes = ['size', 'color'];
        foreach ($attributes as $attrCode) {
            if ($attribute = $resource->getAttribute($attrCode)) {
                $value = $product->getDataUsingMethod($attrCode);
                if ($value === null || $value === false || $value === '' || !is_scalar($value)) {
                    continue;
                }
                if ($attribute->usesSource()) {
                    if ($value = $attribute->getSource()->getOptionText($value)) {
                        $data[$attrCode] = $value;
                    }
                } else {
                    if ($attribute->getBackendType() == 'int') {
                        $value = (int)$value;
                    } elseif ($attribute->getBackendType() == 'decimal') {
                        $value = (float)$value;
                    }
                    $data[$attrCode] = $value;
                }
            }
        }

        $websiteId = $product->getStore()->getWebsiteId();
        $data['stock'] = $this->_getProductStockQty($product, $websiteId);

        return $data;
    }
}

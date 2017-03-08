<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\ViewInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer as CatalogLayer;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Category;
use Driveback\DigitalDataLayer\Model\DataType\Product as ProductDataType;
use Driveback\DigitalDataLayer\Helper\Data as DigitalDataLayerHelper;

/**
 * @see Magento\GoogleTagManager\Block\ListJson
 *
 * Class Listing
 */
class Listing implements DataTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CatalogLayer
     */
    protected $_catalogLayer;

    /**
     * @var ProductDataType
     */
    protected $_productDataType;

    /**
     * @var DigitalDataLayerHelper
     */
    protected $_digitalDataLayerHelper;

    /**
     * @var string
     */
    protected $_listName;

    /**
     * @var string
     */
    protected $_productListBlockName;

    /**
     * @var bool
     */
    protected $_showCategory = false;

    /**
     * Listing constructor.
     * @param RequestInterface $request
     * @param Registry $registry
     * @param ViewInterface $view
     * @param StoreManagerInterface $storeManager
     * @param LayerResolver $layerResolver
     * @param ProductDataType $product
     * @param DigitalDataLayerHelper $digitalDataLayerHelper
     */
    public function __construct(
        RequestInterface $request,
        Registry $registry,
        ViewInterface $view,
        StoreManagerInterface $storeManager,
        LayerResolver $layerResolver,
        ProductDataType $product,
        DigitalDataLayerHelper $digitalDataLayerHelper
    ) {
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_view = $view;
        $this->_storeManager = $storeManager;
        $this->_catalogLayer = $layerResolver->get();
        $this->_productDataType = $product;
        $this->_digitalDataLayerHelper = $digitalDataLayerHelper;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'listing';
    }

    /**
     * Retrieves a current category
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        $category = null;
        //ignore layer!
        if (false && $this->_catalogLayer) {
            $category = $this->_catalogLayer->getCurrentCategory();
        } elseif ($this->_registry->registry('current_category')) {
            $category = $this->_registry->registry('current_category');
        }

        if ($category && $this->_storeManager->getStore()->getRootCategoryId() == $category->getId()) {
            $category = null;
        }

        return $category;
    }

    /**
     * @param string $listName
     * @return $this
     */
    public function setListName($listName)
    {
        $this->_listName = $listName;
        return $this;
    }

    /**
     * @param string $blockName
     * @return $this
     */
    public function setProductListBlockName($blockName)
    {
        $this->_productListBlockName = $blockName;
        return $this;
    }

    /**
     * @param bool $showCategory
     * @return $this
     */
    public function setShowCategory($showCategory)
    {
        $this->_showCategory = (bool)$showCategory;
        return $this;
    }

    /**
     * @return array
     */
    public function getDigitalDataValue()
    {
        if (!$this->_listName || !$this->_productListBlockName) {
            return null;
        }

        $data = [
            'listId' => 'main',
            'listName' => $this->_listName,
        ];

        if ($this->_showCategory) {
            $category = $this->getCurrentCategory();
            if ($category) {
                $data['categoryId'] = $category->getId();
                $categories = $this->_digitalDataLayerHelper->getCategoryArrayByCategory($category);
                if ($categories !== null) {
                    $data['category'] = $categories;
                }
            }
        }

        $layout = $this->_view->getLayout();
        $productsList = $layout->getBlock($this->_productListBlockName);
        if ($productsList instanceof ListProduct) {
            $products = $productsList->getLoadedProductCollection();
            $items = [];
            foreach ($products as $product) {
                $items[] = $this->_productDataType->getDigitalDataValueByProduct($product);
            }
            $data['items'] = $items;

            if ($toolbarBlockName = $productsList->getToolbarBlockName()) {
                $toolbar = $productsList->getToolbarBlock();
                $data['resultCount'] = $products->getSize();
                $data['pagesCount'] = $toolbar->getLastPageNum();
                $data['currentPage'] = $toolbar->getCurrentPage();

                $sortBy = $toolbar->getCurrentOrder();
                $availableOrders = $toolbar->getAvailableOrders();
                $data['sortBy'] = $availableOrders[$sortBy];
                $data['sortType'] = $toolbar->getCurrentDirection() == 'asc' ? 'Ascending' : 'Descending';
            }
        }

        return $data;
    }
}

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
     * @return array
     */
    public function getDigitalDataValue()
    {
        $listName = $this->_registry->registry('listing_list_name');
        $productListBlockName = $this->_registry->registry('listing_product_list_block_name');
        $showCategory = $this->_registry->registry('listing_show_category');

        if (!$listName || !$productListBlockName) {
            return null;
        }

        $data = [
            'listId' => 'main',
            'listName' => $listName,
        ];

        $displayProducts = true;
        if ($showCategory) {
            $category = $this->getCurrentCategory();
            if ($category) {
                $data['categoryId'] = $category->getId();
                $categories = $this->_digitalDataLayerHelper->getCategoryArrayByCategory($category);
                if ($categories !== null) {
                    $data['category'] = $categories;
                }
                if ($listName == 'category' && $category->getDisplayMode() == Category::DM_PAGE) {
                    $displayProducts = false;
                }
            }
        }

        $productsList = $this->_view->getLayout()->getBlock($productListBlockName);
        if ($displayProducts && $productsList instanceof ListProduct) {
            $products = $productsList->getLoadedProductCollection();
            $items = [];
            foreach ($products as $product) {
                $items[] = $this->_productDataType->getDigitalDataValueByProduct($product);
            }
            $data['items'] = $items;

            if ($listName == 'search') {
                if ($query = $this->_request->getParam(\Magento\Search\Model\QueryFactory::QUERY_VAR_NAME)) {
                    $data['query'] = $query;
                }
            }

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

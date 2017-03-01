<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Driveback\DigitalDataLayer\Helper\Data as DigitalDataLayerHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Class Page
 */
class Page implements DataTypeInterface
{
    /**
     * @var DigitalDataLayerHelper
     */
    protected $_digitalDataLayerHelper;

    /**
     * @var CatalogHelper
     */
    protected $_catalogHelper;

    /**
     * Page constructor.
     * @param DigitalDataLayerHelper $digitalDataLayerHelper
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        DigitalDataLayerHelper $digitalDataLayerHelper,
        CatalogHelper $catalogHelper
    ) {
        $this->_digitalDataLayerHelper = $digitalDataLayerHelper;
        $this->_catalogHelper = $catalogHelper;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'page';
    }

    /**
     * @return array
     */
    public function getDigitalDataValue()
    {
        $currentPageType = $this->_digitalDataLayerHelper->getCurrentPageType();
        $data = ['type' => $currentPageType->getTypeCode()];

        if ($breadcrumbs = $this->_getBreadcrumbs()) {
            $data['breadcrumb'] = $breadcrumbs;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        $result = [];
        $breadcrumbPath = $this->_catalogHelper->getBreadcrumbPath();
        foreach ($breadcrumbPath as $name => $breadcrumb) {
            if (strpos($name, 'category') === 0) {
                $result[] = $breadcrumb['label'];
            }
        }

        return $result;
    }
}

<?php

namespace Driveback\DigitalDataLayer\Plugin;

use Magento\Customer\CustomerData\SectionPool;
use Driveback\DigitalDataLayer\Model\DataType\Cart as DataTypeCart;

class CustomerSectionPool
{
    /**
     * @var DataTypeCart
     */
    protected $_dataTypeCart;

    /**
     * SectionPool constructor.
     * @param DataTypeCart $dataTypeCart
     */
    public function __construct(DataTypeCart $dataTypeCart)
    {
        $this->_dataTypeCart = $dataTypeCart;
    }

    /**
     * @param array $result
     * @return array
     */
    protected function _getSectionsData($result)
    {
        if (isset($result['cart'])) {
            $result['ddl_cart'] = $this->_dataTypeCart->getDigitalDataValue();
        }
        return $result;
    }

    /**
     * @param SectionPool $sectionPool
     * @param array $result
     * @return array
     */
    public function afterGetSectionsData(SectionPool $sectionPool, $result)
    {
        return $this->_getSectionsData($result);
    }

    /**
     * @param SectionPool $sectionPool
     * @param array $result
     * @return array
     */
    public function afterGetSectionDataByNames(SectionPool $sectionPool, $result)
    {
        return $this->_getSectionsData($result);
    }

    /**
     * @param SectionPool $sectionPool
     * @param array $result
     * @return array
     */
    public function afterGetAllSectionData(SectionPool $sectionPool, $result)
    {
        return $this->_getSectionsData($result);
    }
}

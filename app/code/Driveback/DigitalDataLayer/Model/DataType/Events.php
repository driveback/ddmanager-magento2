<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

/**
 * Class Events
 */
class Events implements DataTypeInterface
{
    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        JsonHelper $jsonHelper
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'events';
    }

    /**
     * @return null|array
     */
    public function getDigitalDataValue()
    {
        $events = [];

        if ($productsToAdd = $this->_cookieManager->getCookie(DataHelper::COOKIE_ADD_TO_CART)) {
            $productsToAdd = rawurldecode($productsToAdd);
            $productsToAdd = json_decode($productsToAdd, true);
            foreach ($productsToAdd as $data) {
                $events[] = [
                    'category' => 'Ecommerce',
                    'name' => 'Added Product',
                    'product' => $data['product'],
                    'quantity' => $data['quantity'],
                ];
            }
            $this->_cookieManager->deleteCookie(DataHelper::COOKIE_ADD_TO_CART);
        }

        if ($productsToRemove = $this->_cookieManager->getCookie(DataHelper::COOKIE_REMOVE_FROM_CART)) {
            $productsToRemove = rawurldecode($productsToRemove);
            $productsToRemove = json_decode($productsToRemove, true);
            foreach ($productsToRemove as $data) {
                $events[] = [
                    'category' => 'Ecommerce',
                    'name' => 'Removed Product',
                    'product' => $data['product'],
                    'quantity' => $data['quantity'],
                ];
            }
            $this->_cookieManager->deleteCookie(DataHelper::COOKIE_REMOVE_FROM_CART);
        }

        return $events ? $events : null;
    }
}

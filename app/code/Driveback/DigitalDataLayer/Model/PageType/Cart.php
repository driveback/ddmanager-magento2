<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\App\RequestInterface;

/**
 * Class Cart
 */
class Cart implements PageTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Cart constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * @return string
     */
    public function getTypeCode()
    {
        return 'cart';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        return $this->_request->getRouteName() == 'checkout'
        && $this->_request->getControllerName() == 'cart';
    }
}

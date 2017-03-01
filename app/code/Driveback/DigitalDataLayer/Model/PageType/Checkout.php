<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\App\RequestInterface;

/**
 * Class Checkout
 */
class Checkout implements PageTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Checkout constructor.
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
        return 'checkout';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        return $this->_request->getRouteName() == 'checkout'
        && ($this->_request->getControllerName() == 'index'
            || $this->_request->getControllerName() == 'onepage'
            && $this->_request->getActionName() != 'success'
        );

    }
}

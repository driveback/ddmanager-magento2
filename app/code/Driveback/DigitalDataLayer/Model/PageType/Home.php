<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\App\RequestInterface;

/**
 * Class Home
 */
class Home implements PageTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Home constructor.
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
        return 'home';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        return $this->_request->getRouteName() == 'cms'
        && $this->_request->getControllerName() == 'index'
        && $this->_request->getActionName() == 'index';
    }
}

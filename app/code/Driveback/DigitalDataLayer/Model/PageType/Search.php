<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\App\RequestInterface;

/**
 * Class Search
 */
class Search implements PageTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Search constructor.
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
        return 'search';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        return $this->_request->getRouteName() == 'catalogsearch';
    }
}

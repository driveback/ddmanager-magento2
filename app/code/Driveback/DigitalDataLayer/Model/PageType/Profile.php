<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\App\ViewInterface;

/**
 * Class Profile
 */
class Profile implements PageTypeInterface
{
    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * Profile constructor.
     * @param ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        $this->_view = $view;
    }

    /**
     * @return string
     */
    public function getTypeCode()
    {
        return 'profile';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        $config = $this->_view->getPage()->getConfig();
        $bodyElementAttributes = $config->getElementAttributes($config::ELEMENT_TYPE_BODY);

        if (!empty($bodyElementAttributes['class'])) {
            $imploded = explode(' ', $bodyElementAttributes['class']);
            if (in_array('ddl-profile', $imploded)) {
                return true;
            }
        }

        return false;
    }
}

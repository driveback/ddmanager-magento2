<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

/**
 * Class Content
 */
class Content implements PageTypeInterface
{
    /**
     * @return string
     */
    public function getTypeCode()
    {
        return 'content';
    }

    /**
     * @return bool
     */
    public function isCurrentPageType()
    {
        return true;
    }
}

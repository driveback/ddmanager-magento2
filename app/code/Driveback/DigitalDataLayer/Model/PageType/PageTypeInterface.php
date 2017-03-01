<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

/**
 * Class PageTypeInterface
 */
interface PageTypeInterface
{
    /**
     * @return string
     */
    public function getTypeCode();

    /**
     * @return bool
     */
    public function isCurrentPageType();
}

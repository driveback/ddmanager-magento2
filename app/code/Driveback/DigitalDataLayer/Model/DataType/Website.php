<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

/**
 * Class Website
 */
class Website implements DataTypeInterface
{
    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'website';
    }

    /**
     * @return array
     */
    public function getDigitalDataValue()
    {
        return ['type' => 'desktop'];
    }
}

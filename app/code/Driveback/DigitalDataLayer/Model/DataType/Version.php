<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

/**
 * Class Version
 */
class Version implements DataTypeInterface
{
    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'version';
    }

    /**
     * @return string
     */
    public function getDigitalDataValue()
    {
        return '1.1.2';
    }
}

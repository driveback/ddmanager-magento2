<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

/**
 * Class DataTypeInterface
 */
interface DataTypeInterface
{
    /**
     * @return string
     */
    public function getDigitalDataKey();

    /**
     * @return mixed
     */
    public function getDigitalDataValue();
}

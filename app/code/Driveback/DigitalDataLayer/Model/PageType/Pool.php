<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Pool
 */
class Pool
{
    /**
     * @var PageTypeFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_types = [];

    /**
     * @var PageTypeInterface[]
     */
    protected $_typesInstances = null;

    /**
     * Pool constructor.
     * @param PageTypeFactory $factory
     * @param array $types
     */
    public function __construct(
        PageTypeFactory $factory,
        array $types
    ) {
        $this->_types = $types;
        $this->_factory = $factory;
    }

    /**
     * List of types
     *
     * @return object[]
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * Retrieve instantiated
     *
     * @return PageTypeInterface[]
     * @throws LocalizedException
     */
    public function getTypesInstances()
    {
        if ($this->_typesInstances === null) {
            $typeInstances = [];
            foreach ($this->_types as $type) {
                if (empty($type['class'])) {
                    throw new LocalizedException(__('Parameter "class" must be present.'));
                }

                if (empty($type['sortOrder'])) {
                    throw new LocalizedException(__('Parameter "sortOrder" must be present.'));
                }

                $typeInstances[$type['class']] = $this->_factory->create($type['class']);
            }
            $this->_typesInstances = $typeInstances;
        }

        return $this->_typesInstances;
    }

    /**
     * Sorting according to sort order
     *
     * @param array $data
     * @return array
     */
    protected function sort(array $data)
    {
        usort($data, function (array $a, array $b) {
            $a['sortOrder'] = $this->getSortOrder($a);
            $b['sortOrder'] = $this->getSortOrder($b);

            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            }

            return ($a['sortOrder'] < $b['sortOrder']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Retrieve sort order from array
     *
     * @param array $variable
     * @return int
     */
    protected function getSortOrder(array $variable)
    {
        return !empty($variable['sortOrder']) ? $variable['sortOrder'] : 0;
    }
}

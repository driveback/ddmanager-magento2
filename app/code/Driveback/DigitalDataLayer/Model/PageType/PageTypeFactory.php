<?php

namespace Driveback\DigitalDataLayer\Model\PageType;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class PageTypeFactory
 */
class PageTypeFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create model
     *
     * @param string $className
     * @param array $data
     * @return PageTypeInterface
     * @throws \InvalidArgumentException
     */
    public function create($className, array $data = [])
    {
        $model = $this->objectManager->create($className, $data);

        if (!$model instanceof PageTypeInterface) {
            throw new \InvalidArgumentException(
                'Type "' . $className . '" is not instance on ' . PageTypeInterface::class
            );
        }

        return $model;
    }
}

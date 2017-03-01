<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResource;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order;

/**
 * Class User
 */
class User implements DataTypeInterface
{
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var SubscriberResource
     */
    protected $_subscriberResource;

    /**
     * @var OrderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * User constructor.
     * @param CustomerSession $customerSession
     * @param SubscriberResource $subscriberResource
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        CustomerSession $customerSession,
        SubscriberResource $subscriberResource,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_subscriberResource = $subscriberResource;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'user';
    }

    /**
     * @return bool
     */
    protected function _isSubscribed()
    {
        $customer = $this->_customerSession->getCustomerData();
        $subscriberData = $this->_subscriberResource->loadByCustomerData($customer);
        return !empty($subscriberData);
    }

    /**
     * @return string
     */
    public function getDigitalDataValue()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomerData();

            $order = $this->_getLastOrder();

            $data = [
                'userId' => $customer->getId(),
                'email' => $customer->getEmail(),
                'isLoggedIn' => true,
                'firstName' => $customer->getFirstname(),
                'lastName' => $customer->getLastname(),
                'isSubscribed' => $this->_isSubscribed(),
                'hasTransacted' => !empty($order),
            ];

            if ($order) {
                $data['lastTransactionDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($order->getCreatedAt()));
            }
        } else {
            $data = [
                'isLoggedIn' => false,
            ];
        }

        return $data;
    }

    /**
     * @return Order
     */
    protected function _getLastOrder()
    {
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId());
        $collection->setPageSize(1);
        $collection->setOrder('created_at');
        return count($collection) > 0 ? $collection->getFirstItem() : false;
    }
}

<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Transaction
 */
class Transaction implements DataTypeInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var OrderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * Cart constructor.
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     * @param OrderRepository $orderRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param QuoteRepository $quoteRepository
     * @param Cart $cart
     */
    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        OrderRepository $orderRepository,
        OrderCollectionFactory $orderCollectionFactory,
        QuoteRepository $quoteRepository,
        Cart $cart
    ) {
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_cart = $cart;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'transaction';
    }

    /**
     * @return bool
     */
    protected function _isConfirmationPage()
    {
        return $this->_request->getRouteName() == 'checkout'
        && $this->_request->getControllerName() == 'onepage'
        && $this->_request->getActionName() == 'success';
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _isFirstOrder(OrderInterface $order)
    {
        if ($customerId = $order->getCustomerId()) {
            $orderCollection = $this->_orderCollectionFactory->create();
            $idFieldName = $orderCollection->getResource()->getIdFieldName();
            $orderCollection->addFieldToFilter($idFieldName, ['neq' => $order->getId()]);
            $orderCollection->addFieldToFilter('customer_id', $customerId);

            return $orderCollection->getSize() <= 0;
        }

        return false;
    }

    /**
     * @return null|array
     */
    public function getDigitalDataValue()
    {
        if (!$this->_isConfirmationPage()) {
            return null;
        }

        $orderIds = $this->_checkoutSession->getLastOrderId();
        if (!$orderIds) {
            return null;
        }

        $orderIds = is_array($orderIds) ? $orderIds : [$orderIds];
        reset($orderIds);
        $orderId = current($orderIds);
        if (!$orderId) {
            return null;
        }

        try {
            $order = $this->_orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        try {
            $quote = $this->_quoteRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $data = $this->_cart->getDigitalDataValueByQuote($quote, Cart::CHECKOUT_STEP_SUCCESS);

        $data['orderId'] = $order->getIncrementId();
        $data['isFirst'] = $this->_isFirstOrder($order);

        return $data;
    }
}

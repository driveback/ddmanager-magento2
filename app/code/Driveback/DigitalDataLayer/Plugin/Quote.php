<?php

namespace Driveback\DigitalDataLayer\Plugin;

use Magento\Framework\Registry;
use Magento\Checkout\Model\Session as CheckoutSession;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

class Quote
{
    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param Registry $registry
     */
    public function __construct(
        DataHelper $dataHelper,
        CheckoutSession $checkoutSession,
        Registry $registry
    ) {
        $this->_helper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Quote\Model\Quote $result
     * @return \Magento\Quote\Model\Quote
     */
    public function afterLoad(\Magento\Quote\Model\Quote $subject, $result)
    {
        /**
         * @var \Magento\Quote\Model\Quote\Item $quoteItem
         */
        if (!$this->_helper->isLayerEnabled()) {
            return $result;
        }

        if ($this->_checkoutSession->hasData(DataHelper::CART_PRODUCT_QUANTITIES)) {
            return $result;
        }

        $productQtys = [];
        foreach ($subject->getAllItems() as $quoteItem) {
            $productQtys[$quoteItem->getId()] = $quoteItem->getQty();
        }

        $this->_checkoutSession->setData(DataHelper::CART_PRODUCT_QUANTITIES, $productQtys);

        return $result;
    }
}

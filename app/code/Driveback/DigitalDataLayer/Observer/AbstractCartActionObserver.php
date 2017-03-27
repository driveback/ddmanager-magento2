<?php

namespace Driveback\DigitalDataLayer\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Registry;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

abstract class AbstractCartActionObserver
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
     * Fired by checkout_cart_product_update_after event
     *
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return $this
     */
    protected function _processQuoteItemsChange($items)
    {
        /**
         * @var \Magento\Quote\Model\Quote\Item $quoteItem
         */
        if (!$this->_helper->isLayerEnabled()) {
            return $this;
        }

        $productsToAdd = $this->_registry->registry('ddl_products_addtocart');
        if (!$productsToAdd) {
            $productsToAdd = [];
        }

        $productsToRemove = $this->_registry->registry('ddl_products_to_remove');
        if (!$productsToRemove) {
            $productsToRemove = [];
        }

        $lastValues = [];
        if ($this->_checkoutSession->hasData(DataHelper::CART_PRODUCT_QUANTITIES)) {
            $lastValues = $this->_checkoutSession->getData(DataHelper::CART_PRODUCT_QUANTITIES);
        }

        foreach ($items as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }
            $oldQty = isset($lastValues[$quoteItem->getId()]) ? $lastValues[$quoteItem->getId()] : 0;
            $qty = $quoteItem->isDeleted() ? 0 : $quoteItem->getQty();
            if ($qty > $oldQty) {
                $productsToAdd[] = [
                    'product' => $quoteItem->getProduct()->getSku(),
                    'quantity' => $qty - $oldQty
                ];
            } elseif ($qty < $oldQty) {
                $productsToRemove[] = [
                    'product' => $quoteItem->getProduct()->getSku(),
                    'quantity' => $oldQty - $qty
                ];
            }
        }

        $this->_registry->unregister('ddl_products_addtocart');
        $this->_registry->register('ddl_products_addtocart', $productsToAdd);

        $this->_registry->unregister('ddl_products_to_remove');
        $this->_registry->register('ddl_products_to_remove', $productsToRemove);

        $this->_checkoutSession->unsetData(DataHelper::CART_PRODUCT_QUANTITIES);

        return $this;
    }
}

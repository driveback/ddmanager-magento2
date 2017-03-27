<?php

namespace Driveback\DigitalDataLayer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CartAddObserver extends AbstractCartActionObserver implements ObserverInterface
{
    /**
     * Fired by sales_quote_product_add_after event
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getEvent()->getItems();
        $this->_processQuoteItemsChange($items);
        return $this;
    }
}

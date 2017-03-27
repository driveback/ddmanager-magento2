<?php

namespace Driveback\DigitalDataLayer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CartRemoveObserver extends AbstractCartActionObserver implements ObserverInterface
{

    /**
     * Fired by sales_quote_remove_item event
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $this->_processQuoteItemsChange([$quoteItem]);
        return $this;
    }
}

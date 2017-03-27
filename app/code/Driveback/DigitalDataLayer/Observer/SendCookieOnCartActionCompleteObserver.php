<?php

namespace Driveback\DigitalDataLayer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Registry;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Driveback\DigitalDataLayer\Helper\Data as DataHelper;

class SendCookieOnCartActionCompleteObserver implements ObserverInterface
{
    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @param DataHelper $helper
     * @param Registry $registry
     * @param CookieManagerInterface $cookieManager
     * @param JsonHelper $jsonHelper
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        DataHelper $helper,
        Registry $registry,
        CookieManagerInterface $cookieManager,
        JsonHelper $jsonHelper,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_cookieManager = $cookieManager;
        $this->_jsonHelper = $jsonHelper;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Send cookies after cart action
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helper->isLayerEnabled()) {
            return $this;
        }

        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(3600)
            ->setPath('/')
            ->setHttpOnly(false);

        $productsToAdd = $this->_registry->registry('ddl_products_addtocart');
        if (!empty($productsToAdd)) {
            $this->_cookieManager->setPublicCookie(
                DataHelper::COOKIE_ADD_TO_CART,
                rawurlencode(json_encode($productsToAdd)),
                $publicCookieMetadata
            );
        }

        $productsToRemove = $this->_registry->registry('ddl_products_to_remove');
        if (!empty($productsToRemove)) {
            $this->_cookieManager->setPublicCookie(
                DataHelper::COOKIE_REMOVE_FROM_CART,
                rawurlencode($this->_jsonHelper->jsonEncode($productsToRemove)),
                $publicCookieMetadata
            );
        }

        return $this;
    }
}

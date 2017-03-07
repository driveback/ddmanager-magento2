<?php

namespace Driveback\DigitalDataLayer\Model\DataType;

use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Cart
 */
class Cart implements DataTypeInterface
{
    const CHECKOUT_STEP_CART = 1;
    const CHECKOUT_STEP_SHIPPING = 2;
    const CHECKOUT_STEP_REVIEW = 3;
    const CHECKOUT_STEP_SUCCESS = 4;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var TaxHelper
     */
    protected $_taxHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * Cart constructor.
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     * @param TaxHelper $taxHelper
     * @param StoreManagerInterface $storeManager
     * @param Product $product
     */
    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        TaxHelper $taxHelper,
        StoreManagerInterface $storeManager,
        Product $product
    ) {
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_taxHelper = $taxHelper;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
    }

    /**
     * @return string
     */
    public function getDigitalDataKey()
    {
        return 'cart';
    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function _getLineItems(Quote $quote)
    {
        $lineItems = [];
        $isTaxIncluded = $this->_taxHelper->displayCartPriceInclTax($quote->getStore());
        foreach ($quote->getAllVisibleItems() as $item) {
            $subtotal = $isTaxIncluded ? $item->getRowTotalInclTax() * 1 : $item->getRowTotal() * 1;
            $lineItems[] = [
                'product' => $this->_product->getDigitalDataValueByProduct($item->getProduct()),
                'quantity' => $item->getQty() * 1,
                'subtotal' => round($subtotal, 2),
                'isTaxIncluded' => $isTaxIncluded,
            ];
        }

        return $lineItems;
    }

    /**
     * @return int
     */
    protected function _getCheckoutStep()
    {
        if ($this->_request->getRouteName() != 'checkout') {
            return self::CHECKOUT_STEP_CART;
        }
        if ($this->_request->getControllerName() == 'cart') {
            return self::CHECKOUT_STEP_CART;
        }
        if ($this->_request->getControllerName() == 'onepage' && $this->_request->getActionName() == 'success') {
            return self::CHECKOUT_STEP_SUCCESS;
        }
        return self::CHECKOUT_STEP_SHIPPING;
    }

    /**
     * @return null|array
     */
    public function getDigitalDataValue()
    {
        $quote = $this->_checkoutSession->getQuote();
        $checkoutStep = $this->_getCheckoutStep();
        if ($checkoutStep == self::CHECKOUT_STEP_SUCCESS) {
            return null;
        }

        return $this->getDigitalDataValueByQuote($quote, $checkoutStep);
    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function _getContactInfo(Quote $quote)
    {
        $contactInfo = [];
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        if ($firstName = $address->getFirstname()) {
            $contactInfo['firstName'] = $firstName;
        }

        if ($lastName = $address->getLastname()) {
            $contactInfo['lastName'] = $lastName;
        }

        if ($phone = $address->getTelephone()) {
            $contactInfo['phone'] = $phone;
        }

        if ($countryId = $address->getCountryId()) {
            $contactInfo['country'] = $address->getCountryModel()->getName();
        }

        if ($city = $address->getCity()) {
            $contactInfo['city'] = $city;
        }

        if ($street = $address->getStreet()) {
            $contactInfo['address'] = implode(PHP_EOL, $street);
        }

        if ($postcode = $address->getPostcode()) {
            $contactInfo['index'] = $postcode;
        }

        return $contactInfo;
    }

    /**
     * @param Quote $quote
     * @param int $checkoutStep
     * @return array
     */
    public function getDigitalDataValueByQuote(Quote $quote, $checkoutStep)
    {
        $lineItems = $this->_getLineItems($quote);

        $data = [];
        if ($quote->getId() && $lineItems) {
            $data['id'] = $quote->getId();
        }

        $data['currency'] = $quote->getQuoteCurrencyCode() ?
            $quote->getQuoteCurrencyCode() : $this->_storeManager->getStore()->getCurrentCurrencyCode();

        $data['subtotal'] = $lineItems ? round($quote->getSubtotal() * 1, 2) : 0;
        $data['total'] = $lineItems ? round($quote->getGrandTotal() * 1, 2) : 0;
        $data['lineItems'] = $lineItems;

        if ($quote->getId() && $lineItems) {
            $data['checkoutStep'] = $checkoutStep;

            if ($couponCode = $quote->getCouponCode()) {
                $data['vouchers'] = [$couponCode];
            }

            if ($checkoutStep != self::CHECKOUT_STEP_CART) {
                if (!$quote->isVirtual()) {
                    $shippingAddress = $quote->getShippingAddress();

                    if ($shippingMethod = $shippingAddress->getShippingMethod()) {
                        if ($shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod)) {
                            $data['shippingCost'] = round($shippingAddress->getShippingAmount() * 1, 2);
                            $title = $shippingRate->getCarrierTitle() . ' - ' . $shippingRate->getMethodTitle();
                            $data['shippingMethod'] = trim($title, '-');
                        }
                    }
                }

                if ($paymentMethod = $quote->getPayment()->getMethod()) {
                    $data['paymentMethod'] = $paymentMethod;
                }

                if ($contactInfo = $this->_getContactInfo($quote)) {
                    $data['contactInfo'] = $contactInfo;
                }
            }
        }

        return $data;
    }
}

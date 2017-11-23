<?php

namespace EMS\Pay\Model\Method;

use EMS\Pay\Model\Currency;
use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Model\Hash;
use EMS\Pay\Model\Response;
use EMS\Pay\Model\Info;
use \Magento\Checkout\Model\Session;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Sales\Model\Order;


abstract class EmsAbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Default precision
     */
    const DEFAULT_PRECISION = 2;

    protected $_infoBlockType = 'EMS\Pay\Block\Payment\Info';
    protected $_formBlockType = 'ems_pay/payment_form_form';

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment config instance
     *
     * @var Config
     */
    protected $_config = null;

    /**
     * @var Hash
     */
    protected $_hashHandler;

    /**
     * @var Currency
     */
    protected $_currency;
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Mapper
     */
    protected $_mapper;

    /**
     * @var Mapper
     */
    protected $_storeManager;

    protected $_store;

    protected $_configFactory;

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $logger;





    /**
     * Depending on magento tax configuration discount may be applied on row total price.
     * EMS gateway expects to be given price for single item instead of row total if qty > 1
     * In some cases when qty for given product is > 1 rowTotal/qty results in price with 3 digits after decimal point
     * Prices with more than 2 digits after decimal point are not accepted by EMS.
     *
     * This array stores amounts used to round item prices that had 3 digits after decimal point that are used to
     * update chargetotal sent to EMS
     *
     * @var array
     */
    protected $_roundingAmounts = [];

    /**
     * Stores current index of cart item fields
     *
     * @var int
     */
    protected $_itemFieldsIndex = 1;

    protected $_scopeConfig;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentData;
    /**
     * @var array
     */

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Currency $currency,
        Hash $hashHandler,
        Session $session,
        Mapper $mapper,
        StoreManagerInterface $storeManager,
        \EMS\Pay\Gateway\Config\ConfigFactory $configFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []

    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection
        );
        $this->_currency = $currency;
        $this->_hashHandler = $hashHandler;
        $this->_session = $session;
        $this->_mapper = $mapper;
        $this->_storeManager = $storeManager;
        $this->_store = $storeManager->getStore();
        $this->_configFactory = $configFactory;
        $this->_config = $this->_getConfig();
        $this->_scopeConfig = $scopeConfig;
        $this->paymentData = $paymentData;
        $this->logger = $logger;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $store = $this->_storeManager->getStore();
        return $store->getUrl('emspay/index/redirect', ['_secure' => true]);
    }

    /**
     * Instantiate order state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     *
     * @return \EMS\Pay\Model\Method\AbstractMethod
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);

        return $this;
    }

    /**
     * Returns payment action
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        /**
         * TODO check if really needed
         */
        return 'authorize';
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->_config->getGatewayUrl();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRedirectFormFields()
    {
        $debugData = [];
        $config = $this->_config;

        try {
            $fields = [
                \EMS\Pay\Model\Info::TXNTYPE => $config->getTxnType(),
                \EMS\Pay\Model\Info::TIMEZONE => $this->_getTimezone(),
                \EMS\Pay\Model\Info::TXNDATETIME => $this->_getTransactionTime(),
                \EMS\Pay\Model\Info::HASH_ALGORITHM => $this->_getHashAlgorithm(),
                \EMS\Pay\Model\Info::HASH => $this->_getHash(),
                \EMS\Pay\Model\Info::STORENAME => $this->_getStoreName(),
                \EMS\Pay\Model\Info::MODE => $config->getDataCaptureMode(),
                \EMS\Pay\Model\Info::CHECKOUTOPTION => $this->_getCheckoutOption(),
                \EMS\Pay\Model\Info::CHARGETOTAL => $this->_getChargeTotal(),
                \EMS\Pay\Model\Info::CURRENCY => $this->_getOrderCurrencyCode(),
                \EMS\Pay\Model\Info::ORDER_ID => $this->_getOrderId(),
                \EMS\Pay\Model\Info::PAYMENT_METHOD => $this->_getPaymentMethod(),
                \EMS\Pay\Model\Info::RESPONSE_FAIL_URL => $this->_store->getUrl('emspay/index/fail', array('_secure' => true)),
                \EMS\Pay\Model\Info::RESPONSE_SUCCESS_URL => $this->_store->getUrl('emspay/index/success', array('_secure' => true)),
                \EMS\Pay\Model\Info::TRANSACTION_NOTIFICATION_URL => $this->_store->getUrl('emspay/index/ipn', array('_secure' => true)),
                \EMS\Pay\Model\Info::LANGUAGE => $this->_getLanguage(),
                \EMS\Pay\Model\Info::BEMAIL => $this->_getOrder()->getCustomerEmail(),
                \EMS\Pay\Model\Info::MOBILE_MODE => $this->_getMobileMode(),
            ];

            $fields = array_merge($fields, $this->_getAddressRequestFields());
            $fields = array_merge($fields, $this->_getMethodSpecificRequestFields());
            $this->_saveTransactionData();
        } catch (\Exception $ex) {
            $debugData['exception'] = $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine();
            $this->_debug($debugData);
            throw $ex;
        }

        $debugData[] = __('Generated redirect form fields');
        $debugData['redirect_form_fields'] = $fields;
        $this->_debug($debugData);

        return $fields;
    }

    /**
     * Generates payment request address fields
     *
     * @return array
     */
    protected function _getAddressRequestFields()
    {
        $fields = [];
        $order = $this->_getOrder();

        $billingAddress = $order->getBillingAddress();
        $fields[Info::BCOMPANY] = $billingAddress->getCompany();
        $fields[Info::BNAME] = $billingAddress->getName();
        $fields[Info::BADDR1] = $billingAddress->getStreet1();
        $fields[Info::BADDR2] = $billingAddress->getStreet2();
        $fields[Info::BCITY] = $billingAddress->getCity();
        $fields[Info::BSTATE] = $billingAddress->getRegion();
        $fields[Info::BCOUNTRY] = $billingAddress->getCountryId();
        $fields[Info::BZIP] = $billingAddress->getPostcode();
        $fields[Info::BPHONE] = $billingAddress->getTelephone();

        $shippingAddress = $order->getShippingAddress();
        $fields[Info::SNAME] = $shippingAddress->getName();
        $fields[Info::SADDR1] = $shippingAddress->getStreet1();
        $fields[Info::SADDR2] = $shippingAddress->getStreet2();
        $fields[Info::SCITY] = $shippingAddress->getCity();
        $fields[Info::SSTATE] = $shippingAddress->getRegion();
        $fields[Info::SCOUNTRY] = $shippingAddress->getCountryId();
        $fields[Info::SZIP] = $shippingAddress->getPostcode();

        return $fields;
    }


    /**
     * Generates cart related (items, shipping fee, discount) payment request fields
     * @var $item \Magento\Sales\Model\Order\Item
     * @return array
     */
    protected function _getCartRequestFields()
    {
        $fields = [];
        $order = $this->_getOrder();

        $fields[Info::SHIPPING] = Info::CART_ITEM_SHIPPING_AMOUNT;
        $fields[Info::VATTAX] = $this->_roundPrice($order->getBaseTaxAmount());
        $fields[Info::SUBTOTAL] = $this->_getSubtotal();

        foreach ($order->getAllVisibleItems() as $item) {
            $fields[Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                $item->getId() . Info::CART_ITEM_FIELD_SEPARATOR .
                $item->getName() . Info::CART_ITEM_FIELD_SEPARATOR .
                (int)$item->getQtyOrdered() . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemPriceInclTax($item) . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemPrice($item) . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemTax($item) . Info::CART_ITEM_FIELD_SEPARATOR .
                Info::CART_ITEM_SHIPPING_AMOUNT;

            $this->_itemFieldsIndex++;
        }

        if ($this->_getRoundingAmount()) { //recalculate totals and hash
            $fields[Info::CHARGETOTAL] = $this->_getChargeTotal();
            $fields[Info::SUBTOTAL] = $this->_getSubtotal();
            $fields[Info::HASH] = $this->_getHash();
        }

        /* another approach of solving rounding issue - rounding amount added as separate cart issue
         * it's not used for now
                if ($this->getRoundingAmount()) {
                    $fields[Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                        $this->getOrderId() . '_rounding' . Info::CART_ITEM_FIELD_SEPARATOR .
                        'Rounding fee' . Info::CART_ITEM_FIELD_SEPARATOR .
                        Info::SHIPPING_QTY . Info::CART_ITEM_FIELD_SEPARATOR .
                        $this->getRoundingAmount() . Info::CART_ITEM_FIELD_SEPARATOR .
                        $this->getRoundingAmount() . Info::CART_ITEM_FIELD_SEPARATOR .
                        0 . Info::CART_ITEM_FIELD_SEPARATOR .
                        Info::CART_ITEM_SHIPPING_AMOUNT;

                    $this->_itemFieldsIndex++;
                }
        */
        $fields[Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
            Info::SHIPPING_FIELD_NAME . Info::CART_ITEM_FIELD_SEPARATOR .
            Info::SHIPPING_FIELD_LABEL . Info::CART_ITEM_FIELD_SEPARATOR .
            Info::SHIPPING_QTY . Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingInclTax()) . Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingAmount()) . Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingTaxAmount()) . Info::CART_ITEM_FIELD_SEPARATOR .
            Info::CART_ITEM_SHIPPING_AMOUNT;;
        $this->_itemFieldsIndex++;

        if ($this->_getDiscountInclTax() != 0) {
            $fields[Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                Info::DISCOUNT_FIELD_NAME . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountLabel() . Info::CART_ITEM_FIELD_SEPARATOR .
                Info::SHIPPING_QTY . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountInclTax() . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscount() . Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountTaxAmount() . Info::CART_ITEM_FIELD_SEPARATOR .
                Info::CART_ITEM_SHIPPING_AMOUNT;;
        }

        return $fields;
    }

    /**
     * Generates payment request fields specific for used method
     *
     * @return array
     */
    protected function _getMethodSpecificRequestFields()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function _getHash()
    {
        return $this->_hashHandler->generateRequestHash(
            $this->_getTransactionTime(),
            $this->_getChargeTotal(),
            $this->_getOrderCurrencyCode()
        );
    }

    /**
     * @return string
     */
    protected function _getHashAlgorithm()
    {
        return $this->_hashHandler->getHashAlgorithm();
    }

    /**
     * Retrieves checkout option
     *
     * @return string
     */
    protected function _getCheckoutOption()
    {
        return $this->_config->getCheckoutOption();
    }

    /**
     * Retrieves payment method code used by ems based on magento code
     *
     * @return string
     */
    protected function _getPaymentMethod()
    {
        return $this->_getMethodCodeMapper()->getEmsCodeByMagentoCode($this->getCode());
    }

    /**
     * @return string
     */
    protected function _getStoreName()
    {
        return $this->_config->getStoreName();
    }

    /**
     * Retrieves timezone from order
     *
     * @return string
     */
    protected function _getTimezone()
    {
        return $this->_getOrder()->getCreatedAtStoreDate()->getTimezone();
    }

    /**
     * @return string
     */
    protected function _getTransactionTime()
    {
        $order = $this->_getOrder();

        return $order->getCreatedAtStoreDate()->toString(Config::TXNDATE_ZEND_DATE_FORMAT);
    }

    /**
     * Retrieves amount to be charged from order
     *
     * @return float|string
     */
    protected function _getChargeTotal()
    {
        return $this->_roundPrice($this->_getOrder()->getBaseGrandTotal() + $this->_getRoundingAmount());
    }

    /**
     * @return float
     */
    protected function _getSubtotal()
    {
        $order = $this->_getOrder();
        return $this->_roundPrice($order->getBaseSubtotal() + $order->getBaseShippingAmount() + $this->_getDiscount() + $this->_getRoundingAmount());
    }

    /**
     * @return float
     */
    protected function _getDiscountInclTax()
    {
        return $this->_roundPrice($this->_getOrder()->getBaseDiscountAmount());
    }

    /**
     * @return float
     */
    protected function _getDiscount()
    {
        $order = $this->_getOrder();
        return $this->_roundPrice($this->_getDiscountInclTax() + $order->getBaseHiddenTaxAmount()); //discount is negative, hidden tax is positive number
    }

    /**
     * @return float
     */
    protected function _getDiscountTaxAmount()
    {
        return $this->_getDiscountInclTax() - $this->_getDiscount();
    }

    /**
     * @return string
     */
    protected function _getDiscountLabel()
    {
        return __('Discount') . ' (' . $this->_getOrder()->getDiscountDescription() . ')';
    }

    /**
     * Returns language code for current store
     *
     * @return string
     */
    protected function _getLanguage()
    {
        return $this->_config->getLanguage();
    }

    /**
     * @return int|float
     */
    protected function _getRoundingAmount()
    {
        $amount = 0;
        foreach ($this->_roundingAmounts as $rounding) {
            $amount += $rounding;
        }

        return $amount;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|int
     */
    protected function _getItemPriceInclTax($item)
    {
        $qty = (int)$item->getQtyOrdered();
        $rowTotal = $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
        $price = $this->_roundPrice($rowTotal / $qty);
        $rowTotalAfterRounding = $price * $qty;
        if ($rowTotalAfterRounding != $rowTotal) {
            $this->_roundingAmounts[$item->getId()] = round(100 * $rowTotalAfterRounding - 100 * $rowTotal) / 100;
        }

        return $price;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|int
     */
    protected function _getItemPrice($item)
    {
        return $this->_roundPrice($item->getBaseRowTotal() / (int)$item->getQtyOrdered());
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|int
     */
    protected function _getItemTax($item)
    {
        return $this->_getItemPriceInclTax($item) - $this->_getItemPrice($item);
    }

    /**
     * Retrieves mobile mode flag value
     *
     * @return string
     */
    protected function _getMobileMode()
    {
        return $this->_config->isMobileMode() ? 'true' : 'false';
    }

    /**
     * @return string
     */
    protected function _getOrderId()
    {
        return $this->_getOrder()->getIncrementId();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        return $this->_session->getLastRealOrder();
    }

    /**
     * Retrieves currency numeric code required by EMS gateway
     *
     * @return int
     */
    protected function _getOrderCurrencyCode()
    {
        $order = $this->_getOrder();

        return $this->_currency->getNumericCurrencyCode($order->getBaseCurrency());
    }

    /**
     * Checks whether payment method can be used with specific currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_config->isCurrencySupported($currencyCode);
    }

    /**
     * Returns human readable payment method name
     *
     * @return string
     */
    protected function _getPaymentMethodName()
    {
        return $this->_mapper->getHumanReadableByEmsCode($this->_getPaymentMethod());
    }

    /**
     * @return string
     */
    protected function _getTextCurrencyCode()
    {
        return $this->_currency->getTextCurrencyCode($this->_getOrderCurrencyCode());
    }

    /**
     * Returns payment method logo file name
     *
     * @return string
     */
    public function getLogoFilename()
    {
        return $this->_config->getLogoFilename();
    }

    /**
     * Saves important information about the transaction for future use
     *
     * @return $this
     */
    protected function _saveTransactionData()
    {
        $data = [
            Info::CURRENCY => $this->_getTextCurrencyCode(),
            Info::CHARGETOTAL => $this->_getChargeTotal(),
            Info::TXNDATETIME => $this->_getTransactionTime(),
            Info::HASH_ALGORITHM => $this->_getHashAlgorithm(),
            Info::PAYMENT_METHOD => $this->_getPaymentMethodName(),
        ];

        $info = $this->getInfoInstance();
        foreach ($data as $key => $value) {
            $info->setAdditionalInformation($key, $value);
        }

        $info->save();
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransactionTimeSentInTransactionRequest()
    {
        $info = $this->getInfoInstance();
        return $info->getAdditionalInformation(Info::TXNDATETIME);
    }

    /**
     * @return string|null
     */
    public function getHashAlgorithmSentInTransactionRequest()
    {
        $info = $this->getInfoInstance();
        return $info->getAdditionalInformation(Info::HASH_ALGORITHM);
    }

    /**
     * @inheritdoc
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            Mage::getModel('core/log_adapter', $this->_config->getLogFile())
                ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                ->log($debugData);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDebugFlag()
    {
        return $this->_config->isDebuggingEnabled();
    }

    /**
     * @return Config
     */
    protected function _getConfig()
    {
        if (null === $this->_config) {
        $store = $this->_storeManager->getStore();
            $this->_config =  $this->_configFactory->create();
            $this->_config->setMethod($this->_code);
            $this->_config->setStoreId(is_object($store) ? $store->getId() : $store);
        }

        return $this->_config;
    }

    /**
     * @return \EMS\Pay\Model\Method\Mapper
     */
    protected function _getMethodCodeMapper()
    {
        return $this->_mapper;
    }

    /**
     * @param $price
     * @param int $precision
     * @return float
     */
    protected function _roundPrice($price, $precision = self::DEFAULT_PRECISION)
    {
        return round($price, $precision);
    }

    /**
     * Adds transaction specific information to payment object.
     * It's ment to be overridden and used by classes that inherit from this one
     *
     * @param Response $transactionResponse
     */
    public function addTransactionData(Response $transactionResponse)
    {
    }

}
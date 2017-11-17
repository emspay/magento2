<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 14.11.17
 * Time: 13:04
 */

namespace EMS\Pay\Model\Method\Cc;

use EMS\Pay\Model\Response;
use EMS\Pay\Model\Method\Mapper;
use EMS\Pay\Model\Currency;
use EMS\Pay\Model\Hash;
use EMS\Pay\Model\Info;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Checkout\Model\Session;


abstract class AbstractMethodCc extends \EMS\Pay\Model\Method\EmsAbstractMethod
{
    /**
     * Name of field used in form
     *
     * @var string
     */
    protected $_cardTypeFieldName = '';
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
     * @param Currency $currency
     * @param Hash $hashHandler
     * @param Session $session
     * @param Mapper $mapper
     * @param StoreManagerInterface $storeManager
     * @param \EMS\Pay\Gateway\Config\ConfigFactory $configFactory
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
            $currency,
            $hashHandler,
            $session,
            $mapper,
            $storeManager,
            $configFactory,
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
        $this->_paymentData = $paymentData;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function _getPaymentMethod()
    {
        return $this->_getMethodCodeMapper()->getEmsCodeByMagentoCode($this->_getCardType());
    }
    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = parent::_getMethodSpecificRequestFields();
        $fields[Info::AUTHENTICATE_TRANSACTION] = $this->_is3DSecureEnabled() ? 'true' : 'false';
        return $fields;
    }
    /**
     * Returns card type used for payment
     *
     * @return string|null
     */
    protected function _getCardType()
    {
        return $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);
    }
    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isAvailable = parent::isAvailable($quote);
        return $isAvailable && count($this->_getEnabledCardTypes()) > 0;
    }
    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $cardType = $data->getData($this->_cardTypeFieldName);
        if ($cardType) {
            $info->setAdditionalInformation($this->_cardTypeFieldName, $cardType);
            $info->setCcType($this->_mapper->getHumanReadableByMagentoCode($cardType));
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        parent::validate();
        $errorMessage = '';
        $cardType = $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);
        if ($cardType === null || $cardType == '') {
            $errorMessage = __('Card type is a required field');
        }
        if ($this->_validateCardType($cardType)) {
            $errorMessage = __('Invalid card type selected');
        }
        if ($errorMessage !== '') {
            throw new \Exception($errorMessage);
        }
        return $this;
    }
    /**
     * @return bool
     */
    protected function _is3DSecureEnabled()
    {
        return false;
    }
    /**
     * @inheritdoc
     */
    public function addTransactionData(Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);
        $info = $this->getInfoInstance();
        $info->setCcType($transactionResponse->getCcBrand());
        $info->setCcLast4($transactionResponse->getCcNumber());
        $info->setCcExpMonth($transactionResponse->getExpMonth());
        $info->setCcExpYear($transactionResponse->getExpYear());
        $info->setCcOwner($transactionResponse->getCcOwner());
        return $this;
    }
    /**
     * Validates whether card type code is valid
     *
     * @param string $code
     * @return bool
     */
    abstract protected function _validateCardType($code);
    /**
     * * Returns list of enabled credit card types
     *
     * @return array
     */
    abstract protected function _getEnabledCardTypes();
}
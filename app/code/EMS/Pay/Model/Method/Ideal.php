<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 14.11.17
 * Time: 13:19
 */

namespace EMS\Pay\Model\Method;

use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Model\Method\Cc\AbstractMethodCc;
use EMS\Pay\Model\Response;
use EMS\Pay\Model\Method\Mapper;
use EMS\Pay\Model\Currency;
use EMS\Pay\Model\Hash;
use EMS\Pay\Model\Info;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Checkout\Model\Session;

class Ideal extends \EMS\Pay\Model\Method\EmsAbstractMethod
{
    const ISSUING_BANK_FIELD_NAME = 'issuing_bank';

    protected $_code = Config::METHOD_IDEAL;

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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;


    /**
     * @param Currency $currency
     * @param \EMS\Pay\Model\HashFactory $hashFactory
     * @param Session $session
     * @param Mapper $mapper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \EMS\Pay\Model\Debugger $debugger
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
     * @internal param Hash $hashHandler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Currency $currency,
        \EMS\Pay\Model\HashFactory $hashFactory,
        Session $session,
        Mapper $mapper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \EMS\Pay\Model\Debugger $debugger,
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
            $hashFactory,
            $session,
            $mapper,
            $timezone,
            $debugger,
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
        $this->hashFactory = $hashFactory;
        $this->_session = $session;
        $this->_mapper = $mapper;
        $this->_storeManager = $storeManager;
        $this->_store = $storeManager->getStore();
        $this->_configFactory = $configFactory;
        $this->_config = $this->_getConfig();
        $this->hash = $this->_initHash();
        $this->_scopeConfig = $scopeConfig;
        $this->_paymentData = $paymentData;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = [];
        $fields[Info::IDEAL_ISSUER_ID] = $this->_getIssuingBankCode();
        return $fields;
    }

    /**
     * @return string|null
     */
    protected function _getIssuingBankCode()
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::ISSUING_BANK_FIELD_NAME);
    }

    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $issuingBank = $data->getAdditionalData(self::ISSUING_BANK_FIELD_NAME);
        if (isset($issuingBank)) {
            $info->setAdditionalInformation(self::ISSUING_BANK_FIELD_NAME, $issuingBank);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        parent::validate();
        if (!$this->_isBankSelectionEnabled()) {
            return $this;
        }
        $errorMessage = '';
        $issuingBankCode = $this->getInfoInstance()->getAdditionalInformation(self::ISSUING_BANK_FIELD_NAME);
        if ($issuingBankCode === null || $issuingBankCode == '') {
            $errorMessage = __('Issuing bank is a required field');
        }
        if ($this->_validateIssuingBankCode($issuingBankCode)) {
            $errorMessage = __('Invalid issuing bank selected');
        }
        if ($errorMessage !== '') {
            throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
        }
        return $this;
    }

    /**
     * @param string $code
     * @return bool
     */
    protected function _validateIssuingBankCode($code)
    {
        return !$this->_config->isIdealIssuingBankCodeValid($code);
    }

    /**
     * @return bool
     */
    protected function _isBankSelectionEnabled()
    {
        return $this->_config->isIdealIssuingBankSelectionEnabled();
    }
    /**
     * @inheritdoc
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        $isApplicable = parent::isApplicableToQuote($quote, $checksBitMask);
        if ($isApplicable === false) {
            return false;
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->_currency->isCurrencySupportedByIdeal($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function addTransactionData(\EMS\Pay\Model\Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation(Info::ACCOUNT_OWNER_NAME, $transactionResponse->getAccountOwnerName());
        $info->setAdditionalInformation(Info::IBAN, $transactionResponse->getIban());
        return $this;
    }


}
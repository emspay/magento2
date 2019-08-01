<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 14.11.17
 * Time: 13:19
 */

namespace EMS\Pay\Model\Method;

use EMS\Pay\Model\Currency;
use EMS\Pay\Model\HashFactory;
use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Gateway\Config\ConfigFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Helper\Data;

class EmsDefaultMethod extends EmsAbstractMethod
{
    /**
     * @var string
     */
    protected $_code = Config::CONFIG_GENERAL;

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
     * @var
     */
    protected $_hashFactory;

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
        HashFactory $hashFactory,
        Session $session,
        Mapper $mapper,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        ConfigFactory $configFactory,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $currency,
            $hashFactory,
            $session,
            $mapper,
            $timezone,
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
        $this->_hashFactory = $hashFactory;
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
}

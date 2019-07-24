<?php

namespace EMS\Pay\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use EMS\Pay\Gateway\Config\Config;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var
     */
    protected $_logger;

    /**
     * @var ConfigInterface
     */
    protected $_resourceConfig;

    /**
     * UpgradeData constructor.
     * @param ConfigInterface $resourceConfig
     */
    public function __construct(
        ConfigInterface $resourceConfig)
    {
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * run this code if the module version stored in database is less than 1.0.2
         * i.e. the code is run while upgrading the module from version 1.0.1 to 1.0.2
         */
        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $data = [
                Config::METHOD_CC,
                Config::METHOD_KLARNA,
                Config::METHOD_PAYPAL,
                Config::METHOD_SOFORT,
                Config::METHOD_MASTER_PASS,
                Config::METHOD_MAESTRO,
                Config::METHOD_IDEAL,
                Config::METHOD_BANCONTACT
            ];

            foreach ($data as $item){
                $configPath = 'payment/' . $item . '/data_capture_mode';
                $this->_resourceConfig->saveConfig(
                    $configPath,
                    Config::DATA_TRANSFER_FULLPAY,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    Store::DEFAULT_STORE_ID
                );
            }
        }

        $setup->endSetup();
    }
}

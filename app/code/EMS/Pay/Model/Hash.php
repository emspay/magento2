<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 13:48
 */

namespace Magento\EMS\Pay\Model;

use \Magento\EMS\Pay\Gateway\Config\Config;

class Hash
{
    const HASH_ALGORITHM_SHA256 = 'SHA256';

    /**
     * @var EMS_Pay_Model_Config
     */
    protected $_config;

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }
    /**
     * Generates payment gateway request hash
     *
     * @param string $transactionTime
     * @param string $chargeTotal
     * @param string $currencyCode
     * @return string string
     */
    public function generateRequestHash($transactionTime, $chargeTotal, $currencyCode)
    {
        return hash(
            $this->getHashAlgorithm(),
            bin2hex(
                $this->_getStoreName() .
                $transactionTime .
                $chargeTotal .
                $currencyCode .
                $this->_getSharedSecret()
            )
        );
    }
    /**
     * Generates payment gateway notification hash
     *
     * @param string $hashAlgorithm
     * @param string $transactionTime
     * @param string $chargeTotal
     * @param string $currencyCode
     * @param string $approvalCode
     * @return string
     */
    public function generateNotificationHash($hashAlgorithm, $transactionTime, $chargeTotal, $currencyCode, $approvalCode)
    {
        return hash(
            $hashAlgorithm,
            bin2hex(
                $chargeTotal .
                $this->_getSharedSecret() .
                $currencyCode .
                $transactionTime .
                $this->_getStoreName() .
                $approvalCode
            )
        );
    }
    /**
     * Generates payment gateway response hash
     *
     * @param string $hashAlgorithm
     * @param string $transactionTime
     * @param string $chargeTotal
     * @param string $currencyCode
     * @param string $approvalCode
     * @return string
     */
    public function generateResponseHash($hashAlgorithm, $transactionTime, $chargeTotal, $currencyCode, $approvalCode)
    {
        return hash(
            $hashAlgorithm,
            bin2hex(
                $this->_getSharedSecret() .
                $approvalCode .
                $chargeTotal .
                $currencyCode .
                $transactionTime .
                $this->_getStoreName()
            )
        );
    }
    /**
     * @return string
     */
    public function getHashAlgorithm()
    {
        return self::HASH_ALGORITHM_SHA256;
    }
    /**
     * @return string
     */
    protected function _getStoreName()
    {
        return $this->_config->getStoreName();
    }
    /**
     * @return string
     */
    protected function _getSharedSecret()
    {
        return $this->_config->getSharedSecret();
    }
}
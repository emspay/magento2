<?php
/**
 * Created by PhpStorm.
 * User: algol
 * Date: 04.12.2017
 * Time: 0:11
 */

namespace EMS\Pay\Model;


class Debugger
{
    public static function debug($msg, $file, $priority = \Zend\Log\Logger::DEBUG) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/' . $file . '.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->log($priority, $msg);
    }
}
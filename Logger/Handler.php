<?php
/**
 * Magento 2 extension to send new daily customers by email to merchant
 *
 * @category   Reporting & Analytics
 * @package    WaPoNe_CustomersCounter
 * @author     Michele Fantetti
 * @copyright  Copyright (c) 2020 WaPoNe (https://www.fantetti.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace WaPoNe\CustomersCounter\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/wapone_customerscounter.log';
}
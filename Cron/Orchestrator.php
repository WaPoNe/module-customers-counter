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

namespace WaPoNe\CustomersCounter\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use WaPoNe\CustomersCounter\Logger\Logger;
use WaPoNe\CustomersCounter\Model\Customers;
use WaPoNe\CustomersCounter\Model\Sender;

/**
 * Class Orchestrator
 * @package WaPoNe\CustomersCounter\Cron
 */
class Orchestrator
{
    const PATH_EXTENSION_STATUS = 'customers_counter/general/status';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Customers
     */
    private $customers;
    /**
     * @var Sender
     */
    private $sender;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Orchestrator constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Customers $customers
     * @param Sender $sender
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Customers $customers,
        Sender $sender,
        Logger $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customers = $customers;
        $this->sender = $sender;
        $this->logger = $logger;
    }

    /**
     * Generates feed files
     */
    public function run()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteDebug = $website->getId() . ' - ' . $website->getName();

            $moduleStatus =
                $this->scopeConfig->isSetFlag(
                    self::PATH_EXTENSION_STATUS,
                    ScopeInterface::SCOPE_WEBSITE,
                    $website->getCode()
                );
            if (!$moduleStatus) {
                $msg = "Extension disabled for Website: $websiteDebug";
                $this->logger->warning($msg);
                continue;
            }

            $this->logger->debug(":: Start for Website: $websiteDebug ::");

            $ret = $this->customers->countCustomers($website->getId());
            if (!$ret["success"]) {
                return $ret;
            }
            $newDailyCustomers = $ret["newDailyCustomers"];

            // send Email
            $ret = $this->sender->sendEmail($website, $newDailyCustomers);
            if (!$ret["success"]) {
                return $ret;
            }

            $this->logger->debug(":: End for Website: $websiteDebug ::");
        }
    }
}

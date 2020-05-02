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

namespace WaPoNe\CustomersCounter\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use WaPoNe\CustomersCounter\Logger\Logger;

/**
 * Class Customers
 * @package WaPoNe\CustomersCounter\Model
 */
class Customers
{
    /**
     * @var CollectionFactory
     */
    private $customerFactory;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Customers constructor.
     *
     * @param CollectionFactory $customerFactory
     * @param Logger $logger
     */
    public function __construct(
        CollectionFactory $customerFactory,
        Logger $logger
    )
    {
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
    }

    /**
     * Get new daily customers collection
     *
     * @param $websiteId
     * @return array
     */
    public function countCustomers($websiteId): array
    {
        $from = date("Y-m-d 00:00:00", strtotime('-1 days'));
        $to = date("Y-m-d 23:59:59", strtotime('-1 days'));

        try {
            $customersCollection = $this->customerFactory->create();
            $customersCollection->addFieldToSelect('firstname');
            $customersCollection->addFieldToSelect('lastname');
            $customersCollection->addFieldToSelect('email');
            $customersCollection->addFieldToSelect('is_active');
            $customersCollection->addFieldToFilter('website_id', array('eq' => $websiteId));
            $customersCollection->addFieldToFilter('created_at', array('gteq' => $from));
            $customersCollection->addFieldToFilter('created_at', array('lteq' => $to));

            //$this->logger->debug("Query:" . $customersCollection->getSelect()->__toString());

            $newDailyCustomers = $customersCollection->getData();
        }
        catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return array("success" => false);
        }

        return array("success" => true, "newDailyCustomers" => $newDailyCustomers);
    }
}

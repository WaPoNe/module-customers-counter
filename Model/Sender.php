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

use Magento\Framework\App\Area;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use WaPoNe\CustomersCounter\Logger\Logger;

/**
 * Class Sender
 * @package WaPoNe\CustomersCounter\Model
 */
class Sender
{
    const PATH_SENDER_EMAIL = 'trans_email/ident_general/email';
    const PATH_SENDER_NAME = 'trans_email/ident_general/name';
    const PATH_RECIPIENTS_EMAIL = 'customers_counter/email/recipients';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Sender constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
    }

    /**
     * Send email to merchant with new daily customers
     *
     * @param $website
     * @param $newDailyCustomers
     * @return array
     */
    public function sendEmail($website, $newDailyCustomers): array
    {
        $recipientsEmail =
            $this->scopeConfig->getValue(self::PATH_RECIPIENTS_EMAIL);

        if ($recipientsEmail)
        {
            // setting Recipients
            $recipients = array();
            $recipientsArray = json_decode($recipientsEmail, true);
            //$this->logger->debug("Recipients Array:" . print_r($recipientsArray, true));
            foreach ($recipientsArray as $recipientArray) {
                $recipients[] = $recipientArray['recipient'];
            }

            // Num of new daily customers
            $numNewDailyCustomers = count($newDailyCustomers);
            $this->logger->debug("Num of new daily customers: $numNewDailyCustomers");
            //$this->logger->debug("new daily customers:" . print_r($newDailyCustomers, true));

            $vars = [
                'website'               => $website,
                'numNewDailyCustomers'  => $numNewDailyCustomers,
                'newDailyCustomers'     => $newDailyCustomers
            ];

            $this->inlineTranslation->suspend();

            // setting Sender
            // email Sender
            $sender['email'] = $this->scopeConfig->getValue(self::PATH_SENDER_EMAIL);
            // name Sender
            $sender['name'] = $this->scopeConfig->getValue(self::PATH_SENDER_NAME);

            try {
                // preparing email
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier(
                        'wapone_customers_counter'
                    )
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_ADMINHTML,
                            'store' => 0
                        ]
                    )
                    ->setTemplateVars($vars)
                    ->setFromByScope($sender)
                    ->addTo($recipients)
                    //->addBcc(['assistenza@ittweb.net'])
                    ->getTransport();

                // sending email
                $transport->sendMessage();
            }
            catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                return array("success" => false, "message" => $exception->getMessage());
            }

            $this->inlineTranslation->resume();
        }

        return array("success" => true);
    }
}

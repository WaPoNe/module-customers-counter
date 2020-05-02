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

namespace WaPoNe\CustomersCounter\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class Recipient
 * @package WaPoNe\CustomersCounter\Block\System\Config
 */
class Recipient extends AbstractFieldArray
{
    const COLUMN_NAME_RECIPIENT = 'recipient';

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        // Adding columns
        $this->addColumn(self::COLUMN_NAME_RECIPIENT,
            array(
                'label' => __('Recipient(s) email'),
                'size' => 28,
                'class' => 'required-entry validate-email'
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Recipient');
    }

    /**
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row) {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }
}

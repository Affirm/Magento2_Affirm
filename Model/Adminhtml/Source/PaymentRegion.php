<?php
namespace Astound\Affirm\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentRegion
 * Source model for the system configuration
 * retrieve payment region (country) options.
 *
 * @package Astound\Affirm\Model\Adminhtml\Source
 */
class PaymentRegion implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'US',
                'label' => __('United States'),
            ],
            [
                'value' => 'CA',
                'label' => __('Canada'),
            ]
        ];
    }
}

<?php
/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace OnePica\Affirm\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * Config provider for the payment method
 *
 * @package OnePica\Affirm\Model\Ui
 */
class ConfigProvider  implements ConfigProviderInterface
{
    /**#@+
     * Define constants
     */
    const CODE = 'affirm_gateway';
    const SUCCESS = 0;
    const FRAUD = 1;
    /**#@-*/

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        self::SUCCESS => __('Success'),
                        self::FRAUD => __('Fraud')
                    ]
                ]
            ]
        ];
    }
}

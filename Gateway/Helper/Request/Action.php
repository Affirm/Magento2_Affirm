<?php
/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Gateway\Helper\Request;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class Action
 */
class Action
{
    /**#@+
     * Define constants
     */
    const API_TRANSACTIONS_PATH = '/api/v1/transactions/';
    const API_CHECKOUT_PATH = '/api/v2/checkout/';
    /**#@-*/

    /**
     * Action
     *
     * @var string
     */
    private $action;

    /**
     * Config
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param string $action
     * @param ConfigInterface $config
     */
    public function __construct(
        $action,
        ConfigInterface $config
    ) {
        $this->action = $action;
        $this->config = $config;
    }

    /**
     * Get request URL
     *
     * @param string $additionalPath
     * @return string
     */
    public function getUrl($additionalPath = '', $storeId = null)
    {
        $gateway = $this->config->getValue('mode', $storeId) == 'sandbox'
            ? \Astound\Affirm\Model\Config::API_URL_SANDBOX
            : \Astound\Affirm\Model\Config::API_URL_PRODUCTION;

        return trim($gateway, '/') . sprintf('%s%s', $this->action, $additionalPath);
    }
}

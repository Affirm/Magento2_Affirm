<?php
/**
 * Affirm
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Affirm
 * @package   Affirm
 * @copyright Copyright (c) 2021 Affirm. All rights reserved.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Affirm\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Affirm\Model\Config;

/**
 * Class AbstractDataBuilder
 */
abstract class AbstractDataBuilder implements BuilderInterface
{
    /**#@+
     * Define constants
     */
    const CHECKOUT_TOKEN = 'checkout_token';
    const TRANSACTION_ID = 'transaction_id';
    const CHARGE_ID = 'charge_id';
    /**#@-*/

    /**
     * Config
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Store manager
     *
     * @var \Magento\Store\App\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Config $configAffirm
    ) {
        $this->config = $config;
        $this->_storeManager = $storeManager;
        $this->affirmPaymentConfig = $configAffirm;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    abstract public function build(array $buildSubject);
}

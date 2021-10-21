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

namespace Astound\Affirm\Gateway\Http;

use Astound\Affirm\Gateway\Helper\Request\Action;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractTransferFactory
 */
abstract class AbstractTransferFactory implements TransferFactoryInterface
{
    /**
     * Config
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Transfer builder
     *
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * Action
     *
     * @var Action
     */
    protected $action;

    /**
     * Store manager
     *
     * @var \Magento\Store\App\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param Action $action
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        Action $action,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->action = $action;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get public API key
     *
     * @param int $storeId
     * @return string
     */
    protected function getPublicApiKey($storeId)
    {
        if(!empty($storeId)){
            return $this->config->getValue('mode', $storeId) == 'sandbox'
                ? $this->config->getValue('public_api_key_sandbox', $storeId)
                : $this->config->getValue('public_api_key_production', $storeId);
        } else {
            return $this->config->getValue('mode') == 'sandbox'
                ? $this->config->getValue('public_api_key_sandbox')
                : $this->config->getValue('public_api_key_production');
        }

    }

    /**
     * Get private API key
     *
     * @param int $storeId
     * @return string
     */
    protected function getPrivateApiKey($storeId)
    {
        if(!empty($storeId)){
            return $this->config->getValue('mode', $storeId) == 'sandbox'
                ? $this->config->getValue('private_api_key_sandbox', $storeId)
                : $this->config->getValue('private_api_key_production', $storeId);
        } else {
            return $this->config->getValue('mode') == 'sandbox'
                ? $this->config->getValue('private_api_key_sandbox')
                : $this->config->getValue('private_api_key_production');
        }
    }

    /**
     * Get store id
     *
     * @return string
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}

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

namespace Affirm\Model\Plugin\MiniCart\AsLowAs;

use Affirm\Model\Config as ConfigProvider;

class ConfigPlugin
{
    /**
     * Colors which could be set in "data-affirm-color".
     *
     * @var array
     */
    protected $dataColors = ['blue', 'black', 'white'];

    /**
     * Config provider
     *
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * Init
     *
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * After get config
     *
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, array $result)
    {
        $config = $this->configProvider->getAllAsLowAsConfig();
        $config['element_id'] = 'als_mcc';
        $config['promo_id'] = '';
        $config['color_id'] = in_array($config['logo'], $this->dataColors) ? $config['logo'] : '';
        return array_merge_recursive($result, $config);
    }
}

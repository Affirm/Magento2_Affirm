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

namespace Astound\Affirm\Model\Method\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Magento\Quote\Model\Quote;

/**
 * Class SpecificationPlugin
 *
 * @package Astound\Affirm\Model\Method\Checks
 */
class SpecificationPlugin
{
    /**
     * @var \Astound\Affirm\Model\Config
     */
    protected $config;

    /**
     * Get config
     *
     * @param \Astound\Affirm\Model\ConfigFactory $configFactory
     */
    public function __construct(\Astound\Affirm\Model\ConfigFactory $configFactory)
    {
        $this->config = $configFactory->create();
    }

    /**
     * Change is applicable implementation
     *
     * @param SpecificationInterface $specification
     * @param callable               $proceed
     * @param MethodInterface        $paymentMethod
     * @param Quote                  $quote
     * @return bool
     */
    public function aroundIsApplicable(
        SpecificationInterface $specification,
        \Closure $proceed,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        $originallyIsApplicable = $proceed($paymentMethod, $quote);
        if (!$originallyIsApplicable) {
            return false;
        }
        if ($quote->getCustomerId() && $paymentMethod->getCode() == ConfigProvider::CODE) {
            if ($this->config->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return true;
            }
            return false;
        }
        return $originallyIsApplicable;
    }
}

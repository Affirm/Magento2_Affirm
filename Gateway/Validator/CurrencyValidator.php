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
namespace Astound\Affirm\Gateway\Validator;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CurrencyValidator
 * This class responsible for the currency validation
 *
 * @package Astound\Gateway\Validators
 */
class CurrencyValidator extends AbstractValidator
{
    /**
     * Injected config object
     *
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    public $config;

    /**
     * Injected config object
     *
     * @var \Magento\Store\Model\StoreManagerInterface;
     */
    public $storeManagerInterface;

    /**
     * Inject config object and result factory
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @param 
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->config = $config;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];
        $currentCurrencyCode = $this->storeManagerInterface->getStore()->getCurrentCurrencyCode();
        if ((int)$this->config->getValue('allowspecificcurrency', $storeId) === 1) {
            $availableCurrencies = explode(
                ',',
                $this->config->getValue('currency', $storeId)
            );

            if (!in_array($currentCurrencyCode, $availableCurrencies)) {
                $isValid = false;
            }
        }
        return $this->createResult($isValid);
    }
}

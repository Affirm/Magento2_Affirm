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

namespace Astound\Affirm\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Payment Block Info class
 *
 * @package Astound\Affirm\Block
 */
class Info extends ConfigurableInfo
{
    /**
     * Changed standard template
     *
     * @var string
     */
    protected $_template = 'Astound_Affirm::payment/info/edit.phtml';

    /**
     * Retrieve translated label
     *
     * @param string $field
     * @return Phrase|string
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Is admin panel
     *
     * @return bool
     */
    protected function isInAdminPanel()
    {
        return $this->_appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    /**
     * Get domain url
     *
     * @return string
     */
    protected function getDomainUrl()
    {
        return $this->_scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox' ?
            'sandbox.affirm.com' : 'www.affirm.com';
    }

    /**
     * Get Public Api Key
     *
     * @return string
     */
    protected function getPublicApiKey()
    {
        return $this->_scopeConfig->getValue('payment/affirm_gateway/mode') == 'sandbox' ?
            $this->_scopeConfig->getValue('payment/affirm_gateway/public_api_key_sandbox') :
            $this->_scopeConfig->getValue('payment/affirm_gateway/public_api_key_production');
    }

    /**
     * Get Loan ID
     *
     * @return string
     */
    protected function getLoanId()
    {
        return $this->getInfo()->getOrder()->getPayment()->getAdditionalInformation('transaction_id')
            ?: $this->getInfo()->getOrder()->getPayment()->getAdditionalInformation('charge_id');
    }

    /**
     * Get admin affirm URL
     *
     * @return string
     */
    protected function getAdminAffirmUrl()
    {
        $loanId = $this->getLoanId();
        return sprintf('https://%s/dashboard/#/details/%s?trk=%s', $this->getDomainUrl(), $loanId,
            $this->getPublicApiKey()
        );
    }

    /**
     * Get frontend affirm URL
     *
     * @return string
     */
    protected function getFrontendAffirmUrl()
    {
        $loanId = $this->getLoanId();
        return sprintf("https://%s/u/#/loans/%s?trk=%s", $this->getDomainUrl(), $loanId, $this->getPublicApiKey());
    }

    /**
     * Retrieve affirm main url
     *
     * @return string
     */
    public function getAffirmMainUrl()
    {
        if ($this->isInAdminPanel()) {
            return $this->getAdminAffirmUrl();
        } else {
            return $this->getFrontendAffirmUrl();
        }
    }
}

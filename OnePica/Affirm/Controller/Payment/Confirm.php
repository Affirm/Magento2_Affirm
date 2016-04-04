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

namespace OnePica\Affirm\Controller\Payment;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use \Magento\Quote\Api\CartManagementInterface;
use \Magento\Checkout\Model\Session;
use \OnePica\Affirm\Model\Checkout;

/**
 * Class Confirm
 *
 * @package OnePica\Affirm\Controller\Payment
 */
class Confirm extends \Magento\Framework\App\Action\Action
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Quote management
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * Affirm checkout instance
     *
     * @var \OnePica\Affirm\Model\Checkout
     */
    protected $checkout;

    /**
     * Inject objects to the Confirm action
     *
     * @param Context                 $context
     * @param CartManagementInterface $quoteManager
     * @param Session                 $checkoutSession
     * @param Checkout                $checkout
     */
    public function __construct(
        Context $context,
        CartManagementInterface $quoteManager,
        Session $checkoutSession,
        Checkout $checkout
    )
    {
        $this->checkout = $checkout;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManager;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $token = $this->getRequest()->getParam('checkout_token');
        if ($token) {
            $this->checkout->place($token);
        }
    }
}

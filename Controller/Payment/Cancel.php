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

namespace Affirm\Controller\Payment;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;

/**
 * Payment cancel action
 *
 * @package Affirm\Controller\Payment
 */
class Cancel extends Action
{
    /**
     * Affirm cancel action
     * redirects to checkout cart in case if customer return from affirm to merchant.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // Redirects customer to checkout cart page.
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout');
        return $resultRedirect;
    }
}

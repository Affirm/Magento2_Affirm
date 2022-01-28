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

namespace Affirm\Model\Plugin\Order;

use \Magento\Sales\Controller\Adminhtml\Order\Create\Save as SaveAction;
use \Magento\Framework\Controller\Result\RedirectFactory ;

/**
 * Class Create
 *
 * @package Affirm\Model\Plugin\Order
 */
class Create
{
    /**
     * Result redirect factory
     *
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $forwardRedirectFactory;

    /**
     * Inject redirect factory
     *
     * @param RedirectFactory $forwardFactory
     */
    public function __construct(RedirectFactory $forwardFactory)
    {
        $this->forwardRedirectFactory = $forwardFactory;
    }

    /**
     * Plugin for save order new order in admin
     *
     * @param SaveAction $controller
     * @param callable   $method
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(SaveAction $controller, \Closure $method)
    {
        $data = $controller->getRequest()->getParam('payment');
        if (isset($data['method']) && $data['method'] == \Affirm\Model\Ui\ConfigProvider::CODE) {
            $resultRedirect = $this->forwardRedirectFactory->create();
            $resultRedirect->setPath('affirm/affirm/error');
            return $resultRedirect;
        }
        return $method();
    }
}

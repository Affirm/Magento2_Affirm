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

namespace OnePica\Affirm\Observer;

/**
 * Class AdminhtmlEditSaveOrderBeforeObserver
 *
 * @package OnePica\Affirm\Observer
 */
class AdminhtmlEditSaveOrderBeforeObserver
{
    /**
     * Handles affirm admin
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getRequest();

        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $param = $observer->getRequest()->getParam('payment');
        if ($param && isset($param['method']) && $param['method'] == \OnePica\Affirm\Model\Ui\ConfigProvider::CODE) {
            $request->initForward()
                ->setModuleName('affirm')
                ->setControllerName('affirm')
                ->setActionName('error')
                ->setDispatched(false);
            return false;
        }
    }
}

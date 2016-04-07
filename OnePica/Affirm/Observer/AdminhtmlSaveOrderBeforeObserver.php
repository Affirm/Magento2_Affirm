<?php
namespace OnePica\Affirm\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AdminhtmlSaveOrderBeforeObserver
 *
 * @package OnePica\Affirm\Observer
 */
class AdminhtmlSaveOrderBeforeObserver implements ObserverInterface
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

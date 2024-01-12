<?php
namespace Astound\Affirm\Model\Adminhtml\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;
use Astound\Affirm\Model\Adminhtml\Feed;


/**
 * Customer Observer Model
 */
class PredispatchObserver implements ObserverInterface
{
    /**
     * @var \Astound\Affirm\Model\Adminhtml\Feed
     */
    protected $_feedFactory;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;
    /**
     * @param \Astound\Affirm\Model\Adminhtml\Feed $feed
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        Feed $feed,
        Session $backendAuthSession
    ) {
        $this->_feedFactory = $feed;
        $this->_backendAuthSession = $backendAuthSession;
    }
    /**
     * Predispath admin action controller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory;
            /* @var $feedModel \Astound|Affirm\Model\Adminhtml|Feed */
            $feedModel->checkUpdate();
        }
    }
}

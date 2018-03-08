<?php
namespace Astound\Affirm\Model\Adminhtml;

use Magento\AdminNotification\Model\Feed as AdminNotificationFeed;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Feed
 *
 * @package Astound\Affirm\Model\Adminhtml
 */
class Feed extends AdminNotificationFeed
{
    /**
     * Feed url config path
     *
     * @var string
     */
    const XML_FEED_URL_PATH = 'system/adminnotification/notification_feed';

    const XML_FEEDS_SUBSCRIBED  = 'payment/affirm_gateway/notification/notification_update';

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        if ($this->_isNotificationSubscribed()) {
            if ($this->getFrequency() + $this->getLastUpdate() > time()) {
                return $this;
            }

            $feedData = [];

            $feedXml = $this->getFeedData();

            if ($feedXml && $feedXml->entry) {
                foreach ($feedXml->entry as $item) {
                    $itemPublicationDate = strtotime((string)$item->updated);
                    $feedData[] = [
                        'severity' => (int)isset($item->severity) ? $item->severity
                            : (string)\Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => 'Affirm Extension Version ' . (string)$item->title . ' is now available',
                        'description' => 'Affirm Extension Version ' . (string)$item->title . ' for Magento is now available for download and upgrade. To see a full list of updates please check the release notes page.',
                        'url' => 'https://github.com/Affirm/Magento2_Affirm/releases', // Affirm Magento 2 extension github releases
                    ];
                    break;
                }

                if ($feedData) {
                    $this->_inboxFactory->create()->parse(array_reverse($feedData));
                }
            }
            $this->setLastUpdate();

            return $this;
        }
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('affirm_admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'affirm_admin_notifications_lastcheck');
        return $this;
    }

    protected function _isNotificationSubscribed()
    {
        return $this->_backendConfig->getValue(self::XML_FEEDS_SUBSCRIBED) == 1;
    }

}

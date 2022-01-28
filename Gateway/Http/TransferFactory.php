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

namespace Affirm\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferInterface;
use Affirm\Gateway\Http\Client\ClientService;

/**
 * Class TransferFactory
 */
class TransferFactory extends AbstractTransferFactory
{
    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $method = isset($request['method']) ? $request['method'] : ClientService::POST;
        // Admin actions will include store id in the request
        $storeId = isset($request['storeId']) ? $request['storeId'] : $this->getStoreId();
        return $this->transferBuilder
            ->setMethod($method)
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setBody($request['body'])
            ->setAuthUsername($this->getPublicApiKey($storeId))
            ->setAuthPassword($this->getPrivateApiKey($storeId))
            ->setUri($this->getApiUrl($request['path'], $storeId))
            ->build();
    }

    /**
     * Get Api url
     *
     * @param string $additionalPath
     * @param string $storeId
     * @return string
     */
    protected function getApiUrl($additionalPath, $storeId)
    {
        return $this->action->getUrl($additionalPath, $storeId);
    }
}

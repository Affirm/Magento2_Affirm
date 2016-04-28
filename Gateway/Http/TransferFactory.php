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
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferInterface;
use Astound\Affirm\Gateway\Http\Client\ClientService;

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
        return $this->transferBuilder
            ->setMethod($method)
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setBody($request['body'])
            ->setAuthUsername($this->getPublicApiKey())
            ->setAuthPassword($this->getPrivateApiKey())
            ->setUri($this->getApiUrl($request['path']))
            ->build();
    }

    /**
     * Get Api url
     *
     * @param string $additionalPath
     * @return string
     */
    protected function getApiUrl($additionalPath)
    {
        return $this->action->getUrl($additionalPath);
    }
}

<?php
namespace Astound\Affirm\Test\Unit\Model;

use Astound\Affirm\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigEnvironmentTest extends TestCase
{
    /**
     * @dataProvider modeProvider
     */
    public function testResolveEnvironment($mode, $environment)
    {
        $this->assertSame($environment, Config::resolveEnvironment($mode));
    }

    /**
     * @dataProvider modeProvider
     */
    public function testApiUrlForMode($mode, $environment, $apiUrl)
    {
        $this->assertSame($apiUrl, Config::getApiUrlForMode($mode));
    }

    public function modeProvider()
    {
        return [
            'sandbox' => ['sandbox', 'sandbox', Config::API_URL_SANDBOX],
            'stage' => ['stage', 'stage', Config::API_URL_STAGE],
            'production' => ['production', 'production', Config::API_URL_PRODUCTION],
            'unset falls back to production' => [null, 'production', Config::API_URL_PRODUCTION],
            'unknown falls back to production' => ['bogus', 'production', Config::API_URL_PRODUCTION],
        ];
    }
}

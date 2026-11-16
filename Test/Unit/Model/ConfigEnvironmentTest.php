<?php
namespace Astound\Affirm\Test\Unit\Model;

use Astound\Affirm\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigEnvironmentTest extends TestCase
{
    public function testResolveEnvironmentKeepsKnownModes()
    {
        $this->assertSame('sandbox', Config::resolveEnvironment('sandbox'));
        $this->assertSame('stage', Config::resolveEnvironment('stage'));
        $this->assertSame('production', Config::resolveEnvironment('production'));
    }

    public function testResolveEnvironmentFallsBackToProduction()
    {
        $this->assertSame('production', Config::resolveEnvironment(null));
        $this->assertSame('production', Config::resolveEnvironment(''));
        $this->assertSame('production', Config::resolveEnvironment('bogus'));
    }

    public function testApiUrlForEachMode()
    {
        $this->assertSame(Config::API_URL_SANDBOX, Config::getApiUrlForMode('sandbox'));
        $this->assertSame(Config::API_URL_STAGE, Config::getApiUrlForMode('stage'));
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode('production'));
    }

    public function testApiUrlFallsBackToProduction()
    {
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode(null));
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode('bogus'));
    }
}

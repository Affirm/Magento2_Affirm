<?php
namespace Astound\Affirm\Test\Unit\Model;

use Astound\Affirm\Model\Adminhtml\Source\ModeAction;
use Astound\Affirm\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigEnvironmentTest extends TestCase
{
    public function testResolveEnvironmentKeepsKnownModes()
    {
        $this->assertSame('sandbox', Config::resolveEnvironment('sandbox'));
        $this->assertSame('stage', Config::resolveEnvironment('stage'));
        $this->assertSame('stage_live', Config::resolveEnvironment('stage_live'));
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
        $this->assertSame(Config::API_URL_STAGE_LIVE, Config::getApiUrlForMode('stage_live'));
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode('production'));
    }

    public function testApiUrlFallsBackToProduction()
    {
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode(null));
        $this->assertSame(Config::API_URL_PRODUCTION, Config::getApiUrlForMode('bogus'));
    }

    public function testStageLiveUsesTheStageApiHost()
    {
        $this->assertSame('https://api.affirm-stage.com', Config::API_URL_STAGE_LIVE);
        $this->assertNotSame(Config::API_URL_STAGE, Config::API_URL_STAGE_LIVE);
    }

    public function testStageLiveModeIsOfferedInTheAdminDropdown()
    {
        $values = array_column((new ModeAction())->toOptionArray(), 'value');

        $this->assertContains('stage_live', $values);
        $this->assertSame(['sandbox', 'stage', 'stage_live', 'production'], $values);
    }
}

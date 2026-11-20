<?php
namespace Astound\Affirm\Test\Unit\Model\Config\Backend;

use Astound\Affirm\Model\Config\Backend\Url;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    private function url(string $value, ?string $secureBaseUrl = 'https://shop.example.com/'): Url
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('getValue')->willReturn($secureBaseUrl);

        $model = new Url(
            $context,
            $this->createMock(Registry::class),
            $config,
            $this->createMock(TypeListInterface::class)
        );
        $model->setData('value', $value);
        return $model;
    }

    public function testEmptyValueFallsBackToDefaultCallbackUrl(): void
    {
        $model = $this->url('   ');
        $model->beforeSave();

        $this->assertSame('https://shop.example.com/affirm/express/shippingtotals', $model->getValue());
    }

    public function testValidHttpsUrlIsAcceptedAndTrimmed(): void
    {
        $model = $this->url('  https://callbacks.example.com/path  ');
        $model->beforeSave();

        $this->assertSame('https://callbacks.example.com/path', $model->getValue());
    }

    public function testIpAddressHostIsAccepted(): void
    {
        $model = $this->url('https://203.0.113.10/affirm/express/shippingtotals');
        $model->beforeSave();

        $this->assertSame('https://203.0.113.10/affirm/express/shippingtotals', $model->getValue());
    }

    /**
     * @dataProvider invalidUrls
     */
    public function testInvalidUrlIsRejected(string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->url($value)->beforeSave();
    }

    public static function invalidUrls(): array
    {
        return [
            'http scheme' => ['http://example.com/callback'],
            'relative path' => ['/affirm/express/shippingtotals'],
            'single label host' => ['https://gee'],
            'no scheme' => ['example.com/callback'],
            'not a url' => ['not a url'],
        ];
    }

    /**
     * @dataProvider unusableSecureBaseUrls
     */
    public function testGeneratedDefaultIsValidatedWhenSecureBaseUrlIsUnusable(?string $secureBaseUrl): void
    {
        $this->expectException(LocalizedException::class);
        $this->url('', $secureBaseUrl)->beforeSave();
    }

    public static function unusableSecureBaseUrls(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
            'http' => ['http://shop.example.com/'],
        ];
    }
}

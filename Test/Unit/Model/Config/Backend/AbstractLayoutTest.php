<?php
namespace Astound\Affirm\Test\Unit\Model\Config\Backend;

use Astound\Affirm\Model\Config\Backend\CartLayout;
use Astound\Affirm\Model\Config\Backend\MinicartLayout;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

class AbstractLayoutTest extends TestCase
{
    private function layout(string $class, string $value)
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));

        $model = new $class(
            $context,
            $this->createMock(Registry::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class)
        );
        $model->setData('value', $value);
        return $model;
    }

    private function afterLoad($model): void
    {
        $method = new \ReflectionMethod($model, '_afterLoad');
        $method->setAccessible(true);
        $method->invoke($model);
    }

    public static function layouts(): array
    {
        return [
            'minicart' => [MinicartLayout::class],
            'cart' => [CartLayout::class],
        ];
    }

    /**
     * @dataProvider layouts
     */
    public function testDefaultLayoutIsReadFromItsFile(string $class): void
    {
        $default = $class::getDefaultLayout();

        $this->assertNotSame('', $default);
        $this->assertSame(rtrim($default), $default);
    }

    /**
     * @dataProvider layouts
     */
    public function testEmptyValueIsSavedEmpty(string $class): void
    {
        $model = $this->layout($class, "  \n ");
        $model->beforeSave();

        $this->assertSame('', $model->getValue());
    }

    /**
     * @dataProvider layouts
     */
    public function testValueUnchangedFromDefaultIsSavedEmpty(string $class): void
    {
        $model = $this->layout($class, $class::getDefaultLayout());
        $model->beforeSave();

        $this->assertSame('', $model->getValue());
    }

    /**
     * @dataProvider layouts
     */
    public function testDefaultPostedWithBrowserLineBreaksIsSavedEmpty(string $class): void
    {
        $posted = str_replace("\n", "\r\n", $class::getDefaultLayout()) . "\r\n";
        $model = $this->layout($class, $posted);
        $model->beforeSave();

        $this->assertSame('', $model->getValue());
    }

    /**
     * @dataProvider layouts
     */
    public function testCustomizedLayoutIsKept(string $class): void
    {
        $custom = '<div class="my-affirm-button"></div>';
        $model = $this->layout($class, $custom);
        $model->beforeSave();

        $this->assertSame($custom, $model->getValue());
    }

    /**
     * @dataProvider layouts
     */
    public function testEmptyStoredValueShowsDefaultInAdmin(string $class): void
    {
        $model = $this->layout($class, '');
        $this->afterLoad($model);

        $this->assertSame($class::getDefaultLayout(), $model->getValue());
    }

    /**
     * @dataProvider layouts
     */
    public function testStoredCustomValueIsNotReplacedOnLoad(string $class): void
    {
        $custom = '<div class="my-affirm-button"></div>';
        $model = $this->layout($class, $custom);
        $this->afterLoad($model);

        $this->assertSame($custom, $model->getValue());
    }
}

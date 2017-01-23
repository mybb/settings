<?php
/**
 * Unit tests for the generic settings store.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Test\Unit;

use Illuminate\Contracts\Auth\Guard;
use MyBB\Settings\Repositories\SettingRepositoryInterface;
use MyBB\Settings\Store;
use PHPUnit\Framework\TestCase;
use \Mockery as m;

class StoreTest extends TestCase
{
    use m\Adapter\PHPUnit\MockeryPHPUnitIntegration;

    private static function getSettingEntries()
    {
        return [
            (object)[
                'user_id' => null,
                'id' => 1,
                'value' => 'Bar',
                'name' => 'Foo',
                'package' => 'mybb/core',
                'can_user_override' => true,
                'default_value' => 'Baz',
                'setting_type' => 'string',
            ],
            (object)[
                'user_id' => null,
                'id' => 2,
                'value' => null,
                'name' => 'Planet',
                'package' => 'mybb/core',
                'can_user_override' => true,
                'default_value' => 'Earth',
                'setting_type' => 'string',
            ],
        ];
    }

    public function testSimpleGet()
    {
        $authMock = m::mock(Guard::class);

        $repositoryMock = m::mock(SettingRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllSettingsAndValues')->once()->andReturn(static::getSettingEntries());

        $settingStore = new Store($repositoryMock, $authMock);

        $this->assertEquals('Bar', $settingStore->get('Foo'));
        $this->assertEquals($settingStore->get('Foo'), $settingStore['Foo']);
    }

    public function testGetNonExistentWithDefault()
    {
        $authMock = m::mock(Guard::class);

        $repositoryMock = m::mock(SettingRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllSettingsAndValues')->once()->andReturn(static::getSettingEntries());

        $settingStore = new Store($repositoryMock, $authMock);

        $this->assertEquals('World', $settingStore->get('Hello', 'World'));
    }

    public function testGetWithSettingDefault()
    {
        $authMock = m::mock(Guard::class);

        $repositoryMock = m::mock(SettingRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllSettingsAndValues')->once()->andReturn(static::getSettingEntries());

        $settingStore = new Store($repositoryMock, $authMock);

        $this->assertEquals('Earth', $settingStore->get('Planet'));
    }

    public function testHas()
    {
        $authMock = m::mock(Guard::class);

        $repositoryMock = m::mock(SettingRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllSettingsAndValues')->once()->andReturn(static::getSettingEntries());

        $settingStore = new Store($repositoryMock, $authMock);

        $this->assertTrue($settingStore->has('Foo'));
        $this->assertTrue(isset($settingStore['Foo']));
        $this->assertFalse($settingStore->has('Testing'));
    }
}

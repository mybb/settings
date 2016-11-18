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
use MyBB\Settings\DatabaseStore;
use MyBB\Settings\Repositories\SettingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use \Mockery as m;

class StoreTest extends TestCase
{
    use m\Adapter\PHPUnit\MockeryPHPUnitIntegration;

    public function testGet()
    {
        $authMock = m::mock(Guard::class);

        $repositoryMock = m::mock(SettingRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllSettingsAndValues')->once()->andReturn([
            (object)[
                'user_id' => null,
                'id' => 1,
                'value' => 'Bar',
                'name' => 'Foo',
                'package' => 'mybb/core',
            ],
        ]);

        $dbStore = new DatabaseStore($authMock, $repositoryMock);

        $this->assertEquals('Bar', $dbStore->get('Foo'));
        $this->assertEquals($dbStore->get('Foo'), $dbStore['Foo']);
    }
}

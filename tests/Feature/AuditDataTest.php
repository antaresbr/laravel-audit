<?php
namespace Antares\Audit\Tests\Feature;

use Antares\Audit\Enums\DataAction;
use Antares\Audit\Models\AuditData;
use Antares\Audit\Tests\Models\Car;
use Antares\Audit\Tests\Models\User;
use Antares\Audit\Tests\TestCase;
use Antares\Audit\Tests\Traits\AdminUserTrait;
use Illuminate\Support\Facades\Config;

class AuditDataTest extends TestCase
{
    use AdminUserTrait;

    private function getLastDataLog($car)
    {
        return AuditData::where([
            'target' => $car->getTable(),
            'target_pk' => $car->id,
        ])->orderBy('id')->get()->last();
    }

    /**
     * @test
     * @depends create_admin_user
     * @depends check_admin_user
     **/
    public function audit_disabled()
    {
        Config::set('audit.enabled', false);

        $car = new Car();
        $car->name = 'Cruze';
        $car->brand = 'GM';
        $car->save();
        $this->assertCount(1, Car::all());
        $this->assertCount(0, AuditData::all());

        Car::destroy($car->id);
        $this->assertCount(0, Car::all());
        $this->assertCount(0, AuditData::all());
    }

    /**
     * @test
     * @depends audit_disabled
     **/
    public function cars_with_no_log()
    {
        $car = new Car();
        $car->auditLog = false;
        $car->name = 'Beetle';
        $car->brand = 'VW';
        $car->save();
        $this->assertCount(1, Car::all());
        $this->assertCount(0, AuditData::all());

        $car->name = 'New Beetle';
        $car->save();
        $this->assertCount(0, AuditData::all());

        $car->delete();
        $this->assertCount(0, Car::all());
        $this->assertCount(0, AuditData::all());
    }

    /**
     * @test
     * @depends cars_with_no_log
     **/
    public function cars_with_log()
    {
        $user = User::find(1);
        $this->be($user);

        $car = new Car();
        $car->name = 'Beetle';
        $car->brand = 'VW';
        $car->save();
        $this->assertCount(1, Car::all());
        $this->assertCount(1, AuditData::all());

        $log = $this->getLastDataLog($car);
        $this->assertInstanceOf(AuditData::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(DataAction::CREATE->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertIsArray($log->data['new']);
        $this->assertEquals($log->data['new']['name'], $car->name);
        $this->assertEquals($log->data['new']['brand'], $car->brand);
        $this->assertNull($log->data['old']);

        $car->wasRecentlyCreated = false;
        $car->name = 'New Beetle';
        $car->update();
        $this->assertCount(2, AuditData::all());

        $log = $this->getLastDataLog($car);
        $this->assertInstanceOf(AuditData::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(DataAction::UPDATE->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertIsArray($log->data['new']);
        $this->assertEquals($log->data['new']['name'], $car->name);
        $this->assertIsArray($log->data['old']);
        $this->assertEquals($log->data['old']['name'], 'Beetle');

        $car->delete();
        $this->assertCount(0, Car::all());
        $this->assertCount(3, AuditData::all());
        $this->assertCount(3, AuditData::where('user_id', $user->id)->get());

        $log = $this->getLastDataLog($car);
        $this->assertInstanceOf(AuditData::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(DataAction::DELETE->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertNull($log->data['new']);
        $this->assertIsArray($log->data['old']);
        $this->assertEquals($log->data['old']['name'], $car->name);
        $this->assertEquals($log->data['old']['brand'], $car->brand);
    }

    /**
     * @test
     * @depends cars_with_log
     **/
    public function cars_with_log_default_user()
    {
        $user_id = 99;
        Config::set('audit.default.user', $user_id);

        $car = new Car();
        $car->name = 'Fusion';
        $car->brand = 'Ford';
        $car->save();
        $this->assertCount(1, AuditData::where('user_id', $user_id)->get());

        $log = $this->getLastDataLog($car);
        $this->assertInstanceOf(AuditData::class, $log);
        $this->assertEquals($user_id, $log->user_id);
        $this->assertEquals(DataAction::CREATE->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertIsArray($log->data['new']);
        $this->assertEquals($log->data['new']['name'], $car->name);
        $this->assertEquals($log->data['new']['brand'], $car->brand);
        $this->assertNull($log->data['old']);

        $car->wasRecentlyCreated = false;
        $car->name = 'Edge';
        $car->update();
        $this->assertCount(2, AuditData::where('user_id', $user_id)->get());

        $log = $this->getLastDataLog($car);
        $this->assertInstanceOf(AuditData::class, $log);
        $this->assertEquals($user_id, $log->user_id);
        $this->assertEquals(DataAction::UPDATE->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertIsArray($log->data['new']);
        $this->assertEquals($log->data['new']['name'], $car->name);
        $this->assertIsArray($log->data['old']);
        $this->assertEquals($log->data['old']['name'], 'Fusion');
    }
}

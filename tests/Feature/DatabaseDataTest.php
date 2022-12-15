<?php
namespace Antares\Audit\Tests\Feature;

use Antares\Audit\Enums\DataAction;
use Antares\Audit\Models\AuditAction;
use Antares\Audit\Models\AuditData;
use Antares\Audit\Tests\Models\Car;
use Antares\Audit\Tests\Models\User;
use Antares\Audit\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseDataTest extends TestCase
{
    /** @test */
    public function create_admin_user()
    {
        $user = User::create([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@admin.org',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('admin'),
        ]);
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function check_admin_user()
    {
        $this->assertCount(1, User::all());
        $user = User::find(1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->name, 'Admin User');
        $this->assertEquals($user->email, 'admin@admin.org');
    }

    /** @test */
    public function cars_with_no_log()
    {
        $car = new Car();
        $car->auditLog = false;
        $car->name = 'Beetle';
        $car->brand = 'VW';
        $car->save();
        $this->assertCount(1, Car::all());
        $this->assertCount(0, AuditData::all());
        $this->assertCount(0, AuditAction::all());

        $car->name = 'New Beetle';
        $car->save();
        $this->assertCount(0, AuditData::all());
        $this->assertCount(0, AuditAction::all());

        $car->delete();
        $this->assertCount(0, Car::all());
        $this->assertCount(0, AuditData::all());
        $this->assertCount(0, AuditAction::all());
    }

    private function getLastDataLog($car)
    {
        return AuditData::where([
            'target' => $car->getTable(),
            'target_pk' => $car->id,
        ])->orderBy('id')->get()->last();
    }

    /** @test */
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
        $this->assertCount(0, AuditAction::all());

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
        $this->assertCount(0, AuditAction::all());

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
        $this->assertCount(0, AuditAction::all());

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
}

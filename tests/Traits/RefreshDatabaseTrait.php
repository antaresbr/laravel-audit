<?php

namespace Antares\Audit\Tests\Traits;

use Antares\Audit\Models\AuditAction;
use Antares\Audit\Models\AuditData;
use Antares\Audit\Tests\Models\Car;
use Antares\Audit\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait RefreshDatabaseTrait
{
    use RefreshDatabase;

    private function assert_refreshed_database()
    {
        $this->assertCount(0, User::all());
        $this->assertCount(0, AuditAction::all());
        $this->assertCount(0, AuditData::all());
        $this->assertCount(0, Car::all());
    }

    /** @test */
    public function flag_migrated_to_false()
    {
        RefreshDatabaseState::$migrated = false;
        $this->assertFalse(RefreshDatabaseState::$migrated);
    }

    /**
     * @test
     * @depends flag_migrated_to_false
     */
    public function refreshed_database()
    {
        $this->assert_refreshed_database();
    }
}

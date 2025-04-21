<?php
namespace Antares\Audit\Tests\Traits;

use Antares\Audit\Tests\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

trait AdminUserTrait
{
    /**
     * Admin User
     *
     * @var User
     */
    protected static $adminUser;

    protected function getAdminUser()
    {
        if (!static::$adminUser) {
            static::$adminUser = User::find(1);
        }
        return static::$adminUser;
    }

    #[Test]
    public function create_admin_user()
    {
        $user = $this->getAdminUser();
        if (!$user) {
            $user = User::create([
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@admin.org',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('admin'),
            ]);
        }
        $this->assertInstanceOf(User::class, $user);
    }

    #[Test]
    public function check_admin_user()
    {
        $this->assertCount(1, User::all());

        $user = $this->getAdminUser();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->name, 'Admin User');
        $this->assertEquals($user->email, 'admin@admin.org');
    }
}

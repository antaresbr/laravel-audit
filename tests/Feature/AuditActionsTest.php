<?php
namespace Antares\Audit\Tests\Feature;

use Antares\Audit\Enums\ActionsAction;
use Antares\Audit\Http\AuditHttpErrors;
use Antares\Audit\Models\AuditAction;
use Antares\Audit\Tests\Models\User;
use Antares\Audit\Tests\TestCase;
use Antares\Audit\Tests\Traits\AdminUserTrait;
use Antares\Http\JsonResponse;

class AuditActionsTest extends TestCase
{
    use AdminUserTrait;

    private function getLastActionLog($user)
    {
        return AuditAction::where([
            'user_id' => $user->id,
        ])->orderBy('id')->get()->last();
    }

    /**
     * @test
     * @depends create_admin_user
     * @depends check_admin_user
     **/
    public function log_access_is_enabled()
    {
        $response = $this->actingAs($this->getAdminUser())->get(config('audit.route.prefix.api') . '/log-access-is-enabled');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => JsonResponse::SUCCESSFUL,
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('logAccessIsEnabled', $json['data']);
        $this->assertTrue($json['data']['logAccessIsEnabled']);
    }

    /**
     * @test
     * @depends log_access_is_enabled
     **/
    public function log_action_access_error()
    {
        $this->actingAs($this->getAdminUser());

        $response = $this->post(config('audit.route.prefix.api') . '/log-access');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => JsonResponse::ERROR,
            'code' => AuditHttpErrors::PARAMETER_NOT_SUPPLIED
        ]);
        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertTrue(in_array('target', $json['data']));
    }

    /**
     * @test
     * @depends log_action_access_error
     **/
    public function log_action_access_successful()
    {
        $user = $this->getAdminUser();
        $this->actingAs($user);

        $reqData = [
            'target' => 'menu/target/tree',
            'data' => [
                'additional_infos' => 'Additional informations to log'
            ]
        ];
        $response = $this->post(config('audit.route.prefix.api') . '/log-access', $reqData);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => JsonResponse::SUCCESSFUL,
        ]);
        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('audit_action', $json['data']);
        $audit_action = $json['data']['audit_action'];
        $this->assertArrayHasKey('user_id', $audit_action);
        $this->assertArrayHasKey('target', $audit_action);
        $this->assertArrayHasKey('action', $audit_action);
        $this->assertArrayHasKey('data', $audit_action);
        $this->assertArrayHasKey('additional_infos', $audit_action['data']);
        $this->assertEquals($user->id, $audit_action['user_id']);
        $this->assertEquals($reqData['target'], $audit_action['target']);
        $this->assertEquals(ActionsAction::ACCESS->value, $audit_action['action']);
        $this->assertEquals($reqData['data']['additional_infos'], $audit_action['data']['additional_infos']);

        $this->assertCount(1, AuditAction::all());
        $log = $this->getLastActionLog($user);
        $this->assertInstanceOf(AuditAction::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(ActionsAction::ACCESS->value, $log->action);
        $this->assertJson($log->getRawOriginal('data'));
        $this->assertEquals($reqData['data']['additional_infos'], $log->data['additional_infos']);
    }
}

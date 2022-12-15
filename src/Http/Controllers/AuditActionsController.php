<?php
namespace Antares\Audit\Http\Controllers;

use Antares\Http\JsonResponse;
use Antares\Audit\Audit;
use Antares\Audit\Enums\ActionsAction;
use Antares\Audit\Http\AuditHttpErrors;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuditActionsController extends Controller
{
    public function logAccessIsEnabled(Request $request)
    {
        return JsonResponse::successful(['logAccessIsEnabled' => config('audit.actions.log_access', true)]);
    }

    public function logAccess(Request $request)
    {
        $target = $request->input('target');
        if (empty($target)) {
            return JsonResponse::error(AuditHttpErrors::error(AuditHttpErrors::PARAMETER_NOT_SUPPLIED), null, ['target']);
        }

        $data = $request->input('data', []);

        $audit_action = Audit::logAction($request->input('target'), ActionsAction::ACCESS, $data);

        if ($audit_action) {
            return JsonResponse::successful(['audit_action' => $audit_action]);
        } else {
            return JsonResponse::error(AuditHttpErrors::error(AuditHttpErrors::FAIL_TO_LOG_AUDIT_ACTIONS));
        }
    }
}

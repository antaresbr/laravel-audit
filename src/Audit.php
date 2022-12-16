<?php
namespace Antares\Audit;

use Antares\Audit\Enums\ActionsAction;
use Antares\Audit\Enums\DataAction;
use Antares\Audit\Models\AuditAction;
use Antares\Audit\Models\AuditData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit
{
    public static function logData(Model $model, DataAction $action)
    {
        $old = null;
        $new = null;
        if ($action == DataAction::CREATE) {
            $new = $model->getAttributes();
        }
        if ($action == DataAction::DELETE) {
            $old = $model->getOriginal();
        }
        if ($action == DataAction::UPDATE) {
            $new = $model->getChanges();
            foreach (array_keys($new) as $key) {
                $old[$key] = $model->getOriginal($key);
            }
        }
        return AuditData::create([
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'target' => $model->getTable(),
            'target_pk' => $model->getKey(),
            'action' => $action->value,
            'data' => [
                'old' => $old,
                'new' => $new,
            ],
        ]);
    }

    public static function logAction($target, ActionsAction $action, $data = [])
    {
        if ($action == ActionsAction::ACCESS and config('audit.actions.log_access', true) !== true) {
            return null;
        }

        return AuditAction::create([
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'target' => $target,
            'action' => $action->value,
            'data' => $data,
        ]);
    }
}

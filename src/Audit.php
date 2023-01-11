<?php
namespace Antares\Audit;

use Antares\Audit\Enums\ActionsAction;
use Antares\Audit\Enums\DataAction;
use Antares\Audit\Models\AuditAction;
use Antares\Audit\Models\AuditData;
use Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit
{
    public static function getUser()
    {
        return Auth::check() ? Auth::user()->id : config('audit.default.user');
    }

    public static function logData(Model $model, DataAction $action)
    {
        if (config('audit.enabled', true) !== true) {
            return null;
        }
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
        if (property_exists($model, 'auditIgnore') and !empty($model->auditIgnore)) {
            foreach($model->auditIgnore as $key) {
                if (is_array($new) and array_key_exists($key, $new)) {
                    unset($new[$key]);
                }
                if (is_array($old) and array_key_exists($key, $old)) {
                    unset($old[$key]);
                }
            }

        }
        if (!empty($old) or !empty($new)) {
            return AuditData::create([
                'user_id' => static::getUser(),
                'target' => $model->getTable(),
                'target_pk' => $model->getKey(),
                'action' => $action->value,
                'data' => [
                    'old' => $old,
                    'new' => $new,
                ],
            ]);
        }
        return null;
    }

    public static function logAction($target, ActionsAction $action, $data = [])
    {
        if (config('audit.enabled', true) !== true) {
            return null;
        }
        if ($action == ActionsAction::ACCESS and config('audit.actions.log_access', true) !== true) {
            return null;
        }

        return AuditAction::create([
            'user_id' => static::getUser(),
            'target' => $target,
            'action' => $action->value,
            'data' => $data,
        ]);
    }
}

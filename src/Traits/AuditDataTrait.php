<?php
namespace Antares\Audit\Traits;

use Antares\Audit\Audit;
use Antares\Audit\Enums\DataAction;
use Illuminate\Database\Eloquent\Model;

trait AuditDataTrait
{
    /**
     * Flag to log data changes
     *
     * @var bool
     */
    public $auditLog = true;

    /**
     * Boot this trait
     *
     * @return void
     */
    public static function bootAuditDataTrait()
    {
        static::saved(function (Model $model) {
            if (
                (config('audit.enabled', true) !== true) or
                (property_exists($model, 'auditLog') and $model->auditLog === false) or
                (!$model->wasRecentlyCreated and !$model->getChanges())
            ) {
                return;
            }
            Audit::logData($model, ($model->wasRecentlyCreated) ? DataAction::CREATE : DataAction::UPDATE);
        });

        static::deleted(function (Model $model) {
            if (
                (config('audit.enabled', true) !== true) or
                (property_exists($model, 'auditLog') and $model->auditLog === false)
            ) {
                return;
            }
            Audit::logData($model, DataAction::DELETE);
        });
    }
}

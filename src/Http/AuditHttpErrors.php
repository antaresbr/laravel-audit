<?php
namespace Antares\Audit\Http;

use Antares\Http\AbstractHttpErrors;

class AuditHttpErrors extends AbstractHttpErrors
{
    public const UNAUTHENTICATED = 994001;

    public const PARAMETER_NOT_SUPPLIED = 994011;

    public const FAIL_TO_LOG_AUDIT_DATA = 994011;
    public const FAIL_TO_LOG_AUDIT_ACTIONS = 994012;

    public const MESSAGES = [
        self::UNAUTHENTICATED => 'audit::errors.unauthenticated',

        self::PARAMETER_NOT_SUPPLIED => 'audit::errors.parameter_not_supplied',

        self::FAIL_TO_LOG_AUDIT_DATA => 'audit::errors.fail_to_log_audit_data',
        self::FAIL_TO_LOG_AUDIT_ACTIONS => 'audit::errors.fail_to_log_audit_actions',
    ];
}

<?php
namespace Antares\Audit\Enums;

enum DataAction: string
{
    case CREATE = 'CREATE';
    case DELETE = 'DELETE';
    case UPDATE = 'UPDATE';
}

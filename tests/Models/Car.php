<?php

namespace Antares\Audit\Tests\Models;

use Antares\Audit\Traits\AuditDataTrait;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use AuditDataTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'brand',
    ];

    /**
     * Indicates if the model has timestamps.
     *
     * @var bool
     */
    public $timestamps = false;
}

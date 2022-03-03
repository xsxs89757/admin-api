<?php

namespace Qifen\WebmanAdmin\model;

use support\Model;

class AdminModelHasRoles extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_model_has_roles';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];
}
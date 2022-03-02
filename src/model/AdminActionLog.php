<?php

namespace Qifen\WebmanAdmin\model;

use support\Model;

class AdminActionLog extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_action_log';

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
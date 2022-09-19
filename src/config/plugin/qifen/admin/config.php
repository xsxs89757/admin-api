<?php

return [
    'index_url' => 'http://127.0.0.1:8787',

    'access' => \Qifen\WebmanAdmin\middleware\Access::class,
    'action_log' => \Qifen\WebmanAdmin\middleware\ActionLog::class,
];
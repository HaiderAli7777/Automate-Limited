<?php
/* Team area front controller. Every /admin address is routed here by .htaccess. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/admin/kernel.php';
admin_run();

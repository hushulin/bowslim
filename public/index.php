<?php
//----------用户角色ID常量------------
defined('PARENT_ROLEID') or define("PARENT_ROLEID", 1);
defined('TEACHER_ROLEID') or define("TEACHER_ROLEID", 2);
defined('SCHOOLUSER_ROLE') or define("SCHOOLUSER_ROLE", 3);
//----------缓存redis常量------------
defined('REDIS_USER') or define("REDIS_USER", "cache_user_");
defined('REDIS_STUDENT') or define("REDIS_STUDENT", "cache_student_");

// Composer
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';

defined('HOST') or define('HOST', $settings['settings']['host']);

$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Register helpers
require __DIR__ . '/../bow/helpers.php';

// Run app
$app->run();

<?php

require_once './constants.php';
require_once './controllers/EventController.php';

use Controllers\EventController;

$username = $argv[1] ?? null;

if ($username) {
    EventController::fetch($username);
} else {
    echo 'Usage: php github-activity.php {username}'.N;
    exit;
}

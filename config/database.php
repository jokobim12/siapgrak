<?php

/**
 * Database Connection Class - JSON VERSION
 */

require_once ROOT_PATH . '/src/Helpers/JsonDatabase.php';

use App\Helpers\JsonDatabase;

// Helper function to get database instance
function db()
{
    return JsonDatabase::getInstance();
}

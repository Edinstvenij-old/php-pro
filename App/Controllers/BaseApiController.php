<?php

namespace App\Controllers;

use Core\Controller;

class BaseApiController extends Controller
{
    public function before(string $action, array $params = []): bool
    {
        return parent::before($action, $params);
    }
}
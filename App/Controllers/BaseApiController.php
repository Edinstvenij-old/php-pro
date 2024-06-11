<?php

namespace App\Controllers;

use Core\Controller;

class BaseApiController extends Controller
{
    public function before(string $action, array $params = []): bool
    {
        // token (check on expire)
        // get user
        // validate token && check on expire
        // return result (true/false)
    }
}
<?php

namespace src\Components\Auth\Middleware;

use src\Components\Auth\AuthUtil;
use src\Core\PageUtil;
use src\Interfaces\IMiddleware;

class AuthMiddleware implements IMiddleware
{
    public function handle(): int
    {
        if (!AuthUtil::isClientLoggedIn()) {
            PageUtil::redirect("/login");
            return 302;
        }

        return 200;
    }
}

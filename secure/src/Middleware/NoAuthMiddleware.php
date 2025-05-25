<?php

namespace src\Middleware;

use src\Components\Auth\AuthUtil;
use src\Core\PageUtil;
use src\Interfaces\IMiddleware;

class NoAuthMiddleware implements IMiddleware
{
    public function handle(): int
    {
        if (AuthUtil::isClientLoggedIn()) {
            PageUtil::redirect("/");
            return 302;
        }

        return 200;
    }
}

<?php

namespace src\Middleware;

use src\Components\Auth\AuthUtil;
use src\Core\PageUtil;
use src\Interfaces\IMiddleware;
use src\Models\Users;

class AuthMiddleware implements IMiddleware
{
    private const string LOGIN_PATH = "/login";

    public function handle(): int
    {
        if (!AuthUtil::isClientLoggedIn()) {
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }

        if (getCookieValue("cookies_accepted") === "no" && isCookieActive("user")) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }

        return 200;
    }
}

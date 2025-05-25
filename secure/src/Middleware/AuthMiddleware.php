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

        $usersModel = new Users();
        $user = $usersModel->getUserDetails(AuthUtil::getUserData()["id"]);
        if (!$user) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }
        if ($user["is_active"] !== 1) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }
        if ($user["username"] !== AuthUtil::getUserData()["username"]) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }
        if ($user["email"] !== AuthUtil::getUserData()["email"]) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }
        if ($user["full_name"] !== AuthUtil::getUserData()["full_name"]) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }
        if ($user["is_admin"] !== AuthUtil::getUserData()["is_admin"]) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }

        if (getCookieValue("cookies_accepted") === "no" && isCookieActive("user")) {
            AuthUtil::logoutClient();
            PageUtil::redirect(self::LOGIN_PATH);
            return 302;
        }

        if (getCookieValue("cookies_accepted") !== "yes" && getCookieValue("cookies_accepted") !== "no") {
            deleteCookie("cookies_accepted");
        }
        return 200;
    }
}

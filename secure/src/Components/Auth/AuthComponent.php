<?php

namespace src\Components\Auth;

use src\Abstracts\Component;
use src\Core\PageUtil;
use src\Models\Users;

class AuthComponent extends Component
{
    private const string LOGIN_PATH = "/login";

    private static array $settings = [
        "login.enabled" => true,
        "register.enabled" => true,
        "reset_password.enabled" => true,
        "verify_email.enabled" => true,

        "password.min_length" => 8,
        "password.require_uppercase" => true,
        "password.require_number" => true,
        "password.require_special_characters" => true,

        "login.redirect_to" => "/",
        "register.redirect_to" => "/",
        "logout.redirect_to" => "/login",
    ];

    public function __construct()
    {
        $this->actAsController = true;

        if (AuthUtil::isClientLoggedIn()) {
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
        }
    }

    /**
     * Retrieves the settings for the Auth component.
     *
     * This method returns an associative array containing various settings
     * related to authentication, such as enabling login, registration,
     * password reset, email verification, and two-factor authentication.
     * It also includes password policy settings and the login redirect URL.
     *
     * @return array An associative array of authentication settings.
     */
    public static function getSettings(): array
    {
        return self::$settings;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

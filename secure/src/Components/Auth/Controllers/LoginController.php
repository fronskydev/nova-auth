<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\AuthUtil;
use src\Core\PageInfo;
use src\Core\PageUtil;
use src\Models\Users;
use Random\RandomException;

class LoginController extends ComponentController
{
    private const string LOGIN_PATH = "/login";
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function index(): int
    {
        if (!AuthComponent::getSettings()["login.enabled"]) {
            return 404;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Login";
        $this->pageInfo->headerEnabled = false;
        $this->pageInfo->footerEnabled = false;
        $this->pageInfo->styles = [
            "auth.css"
        ];
        $data = [];
        $this->render("login", $data, $this->pageInfo);
        return 200;
    }

    /**
     * @throws RandomException
     */
    public function submit(): int
    {
        if (!AuthComponent::getSettings()["login.enabled"]) {
            return 404;
        }

        if (empty($_POST["csrf"]) || $_POST["csrf"] !== $_ENV["CSRF_TOKEN"]) {
            PageUtil::redirect(self::LOGIN_PATH);
            return 200;
        }

        unset($_POST["csrf"]);
        if (empty($_POST["uid"]) || empty($_POST["password"])) {
            $errorMsg = "Please fill in all fields.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $uid = $_POST["uid"];
        $usersModel = new Users();
        $user = $usersModel->getUserDetails($uid);
        if (!$user) {
            $errorMsg = "Invalid username/email or password.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        if ($user["is_active"] == 0) {
            $errorMsg = "Your account has been disabled. Please contact the support.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        if ($user["is_social"] == 1 && $user["password"] == null && $user["password_salt"] == null) {
            $errorMsg = "This account was created using a social login provider. Please use the same provider to log in.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        if ($user["email_verified"] == 0 && AuthComponent::getSettings()["verify_email.enabled"]) {
            $errorMsg = "Your email address is not verified yet.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $passwordSalt = decryptText($user["password_salt"], "_password_salt");
        $password = $_POST["password"] . $passwordSalt;
        if (!password_verify($password, $user["password"])) {
            $errorMsg = "Invalid username/email or password.";
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $newSalt = generateRandomString(32);
        $newPassword = $_POST["password"] . $newSalt;
        $newPasswordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $usersModel->update($user["id"], array(
            "password_salt" => encryptText($newSalt, "_password_salt"),
            "password" => $newPasswordHash
        ));

        $userData = array(
            "id" => $user["id"],
            "username" => decryptText($user["username"], "_username"),
            "email" => decryptText($user["email"], "_email"),
            "full_name" => decryptText($user["full_name"], "_full_name"),
            "is_admin" => $user["is_admin"]
        );
        AuthUtil::createUserData($userData);
        PageUtil::redirect(AuthComponent::getSettings()["login.redirect_to"]);
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\Enums\AuthTypes;
use src\Core\PageInfo;
use src\Core\PageUtil;
use src\Models\Authentications;
use src\Models\Users;

class ResetPasswordController extends ComponentController
{
    private const string FORGOT_PASSWORD_PATH = "/forgot-password";
    private const string RESET_PASSWORD_PATH = "/reset-password";
    private const string AUTH_SQL_QUERY = "SELECT * FROM authentications WHERE unique_identifier = '%s' AND type = '" . AuthTypes::PASSWORD_RESET->name . "';";
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function index($args): int
    {
        if (!AuthComponent::getSettings()["reset_password.enabled"]) {
            return 404;
        }
        if (empty($args)) {
            return 404;
        }

        $uniqueIdentifier = $args[0];
        $authenticationsModel = new Authentications();
        $authentication = $authenticationsModel->query(str_replace("%s", $uniqueIdentifier, self::AUTH_SQL_QUERY));
        if (!$authentication) {
            return 404;
        }

        $authentication = $authentication[0];
        $createdAt = strtotime($authentication["created_at"]);
        $now = strtotime(date("Y-m-d H:i:s"));
        $difference = $now - $createdAt;
        $usersModel = new Users();
        if ($difference > 900) {
            $user = $usersModel->find($authentication["user_id"]);
            if (!$user) {
                $authenticationsModel->delete($authentication["id"]);
                return 404;
            }

            $authenticationsModel->delete($authentication["id"]);
            $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Reset Password Expired";
            $this->pageInfo->headerEnabled = false;
            $this->pageInfo->footerEnabled = false;
            $this->pageInfo->styles = [
                "auth.css"
            ];
            $data = [];
            $this->render("reset-password-expired", $data, $this->pageInfo);
            return 200;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Reset Password";
        $this->pageInfo->headerEnabled = false;
        $this->pageInfo->footerEnabled = false;
        $this->pageInfo->styles = [
            "auth.css"
        ];
        $data = [
            "authKey" => encryptText($authentication["unique_identifier"])
        ];
        $this->render("reset-password", $data, $this->pageInfo);
        return 200;
    }

    public function submit(): int
    {
        if (!AuthComponent::getSettings()["reset_password.enabled"]) {
            return 404;
        }

        if (empty($_POST["csrf"]) || $_POST["csrf"] !== $_ENV["CSRF_TOKEN"]) {
            if (empty($_POST["auth-key"])) {
                PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
                return 200;
            }

            $uniqueIdentifier = decryptText($_POST["auth-key"]);
            $authenticationsModel = new Authentications();
            $authentication = $authenticationsModel->query(str_replace("%s", $uniqueIdentifier, self::AUTH_SQL_QUERY));
            if (!$authentication) {
                PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
                return 200;
            }

            PageUtil::redirect(self::RESET_PASSWORD_PATH . "/" . $uniqueIdentifier);
            return 200;
        }
        if (empty($_POST["auth-key"])) {
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
            return 200;
        }

        $uniqueIdentifier = decryptText($_POST["auth-key"]);
        $authenticationsModel = new Authentications();
        $authentication = $authenticationsModel->query(str_replace("%s", $uniqueIdentifier, self::AUTH_SQL_QUERY));
        if (!$authentication) {
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
            return 200;
        }
        if (empty($_POST["password"]) || empty($_POST["confirm_password"])) {
            $errorMsg = "Please fill in all fields.";
            PageUtil::redirect(self::RESET_PASSWORD_PATH . "/" . $uniqueIdentifier, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if ($_POST["password"] !== $_POST["confirm_password"]) {
            $errorMsg = "Passwords do not match.";
            PageUtil::redirect(self::RESET_PASSWORD_PATH . "/" . $uniqueIdentifier, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $authentication = $authentication[0];
        $createdAt = strtotime($authentication["created_at"]);
        $now = strtotime(date("Y-m-d H:i:s"));
        if ($now - $createdAt > 900) {
            $authenticationsModel->delete($authentication["id"]);
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
            return 200;
        }

        $usersModel = new Users();
        $user = $usersModel->getUserDetails($authentication["user_id"]);
        if (!$user) {
            $authenticationsModel->delete($authentication["id"]);
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
            return 200;
        }

        $passwordSalt = generateRandomString(16);
        $password = encryptText($_POST["password"] . $passwordSalt);
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $usersModel->update($user["id"], array(
            "password" => $passwordHash,
            "password_salt" => encryptText($passwordSalt)
        ));

        $authenticationsModel->delete($authentication["id"]);
        PageUtil::redirect("/login", array("SUCCESS" => "Your password has been reset successfully. You can now login with your new password."));
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

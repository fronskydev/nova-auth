<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\AuthUtil;
use src\Core\PageUtil;
use src\Mailer\Mailer;
use src\Models\Users;
use Random\RandomException;

class ApiController extends ComponentController
{
    private const string API_BASE_URL = "https://api.fronsky.com";
    private const string LOGIN_PATH = "/login";

    public function index(): int
    {
        if ($_ENV["FRONSKY_API_ENABLED"] !== "true") {
            return 404;
        }

        if (empty($_POST["code"])) {
            return 400;
        }

        $apiUrl = self::API_BASE_URL . "/oauth";
        $data = [
            "code" => $_POST["code"],
            "api_key" => $_ENV["FRONSKY_API_KEY"],
            "api_secret" => $_ENV["FRONSKY_API_SECRET"]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/json",
            "User-Agent: " . $_ENV["APP_NAME"] ."-" . $_ENV["APP_VERSION"]
        ]);

        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            curl_close($ch);
            PageUtil::redirect(self::LOGIN_PATH);
            return 301;
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result["username"], $result["email"], $result["full_name"])) {
            PageUtil::redirect(self::LOGIN_PATH);
            return 301;
        }

        $usersModel = new Users();

        $userEmail = $usersModel->getUserDetails($result["email"]);
        $userUsername = $usersModel->getUserDetails($result["username"]);
        if (empty($userEmail) && empty($userUsername)) {
            $usersModel->create(array(
                "username" => encryptText($result["username"], "_username"),
                "email" => encryptText($result["email"], "_email"),
                "full_name" => encryptText($result["full_name"], "_full_name"),
                "is_active" => 1,
                "is_admin" => 0,
                "is_social" => 1,
                "email_verified" => 1
            ));

            $mail = new Mailer();
            $templateData = [
                "title" => "Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])),
                "user_name" => $result["full_name"],
                "sender_name" => $_ENV["MAIL_FROM_NAME"],
            ];

            $mail->setHtmlTemplate(MAILER_DIR . "/register.html", $templateData)
                ->addSubject("Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])))
                ->addRecipient($result["email"])
                ->setReplyTo($_ENV["MAIL_FROM_ADDRESS"], $_ENV["MAIL_FROM_NAME"])
                ->send();
        }

        if (!empty($userEmail) && !empty($userUsername)) {
            if ($userEmail["id"] !== $userUsername["id"]) {
                $uniqueUsername = $result["username"];
                $counter = 1;
                while (!empty($usersModel->getUserDetails($uniqueUsername))) {
                    $uniqueUsername = $result["username"] . $counter;
                    $counter++;
                }

                $usersModel->update($userEmail["id"], array(
                    "username" => encryptText($uniqueUsername, "_username"),
                    "email" => encryptText($result["email"], "_email"),
                    "full_name" => encryptText($result["full_name"], "_full_name"),
                    "is_active" => 1,
                    "is_admin" => 0,
                    "is_social" => 1,
                    "email_verified" => 1
                ));
            } else {
                $usersModel->update($userEmail["id"], array(
                    "email" => encryptText($result["email"], "_email"),
                    "username" => encryptText($result["username"], "_username"),
                    "full_name" => encryptText($result["full_name"], "_full_name"),
                    "is_active" => 1,
                    "is_admin" => 0,
                    "is_social" => 1,
                    "email_verified" => 1
                ));
            }
        }

        if (!empty($userEmail) && empty($userUsername)) {
            $usersModel->update($userEmail["id"], array(
                "email" => encryptText($result["email"], "_email"),
                "username" => encryptText($result["username"], "_username"),
                "full_name" => encryptText($result["full_name"], "_full_name"),
                "is_active" => 1,
                "is_admin" => 0,
                "is_social" => 1,
                "email_verified" => 1
            ));
        }
        if (empty($userEmail) && !empty($userUsername)) {
            $uniqueUsername = $result["username"];
            $counter = 1;
            while (!empty($usersModel->getUserDetails($uniqueUsername))) {
                $uniqueUsername = $result["username"] . $counter;
                $counter++;
            }

            $usersModel->create(array(
                "username" => encryptText($uniqueUsername, "_username"),
                "email" => encryptText($result["email"], "_email"),
                "full_name" => encryptText($result["full_name"], "_full_name"),
                "is_active" => 1,
                "is_admin" => 0,
                "is_social" => 1,
                "email_verified" => 1
            ));

            $mail = new Mailer();
            $templateData = [
                "title" => "Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])),
                "user_name" => $result["full_name"],
                "sender_name" => $_ENV["MAIL_FROM_NAME"],
            ];

            $mail->setHtmlTemplate(MAILER_DIR . "/register.html", $templateData)
                ->addSubject("Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])))
                ->addRecipient($result["email"])
                ->setReplyTo($_ENV["MAIL_FROM_ADDRESS"], $_ENV["MAIL_FROM_NAME"])
                ->send();
        }

        $usersModel = new Users();
        $user = $usersModel->getUserDetails($result["email"]);
        $userData = array(
            "id" => $user["id"],
            "username" => decryptText($user["username"], "_username"),
            "email" => decryptText($user["email"], "_email"),
            "full_name" => decryptText($user["full_name"], "_full_name"),
            "is_admin" => $user["is_admin"],
        );

        PageUtil::redirect("/api/submit", array("data" => json_encode($userData)));
        return 200;
    }

    /**
     * @throws RandomException
     */
    public function apiSubmit(): int
    {
        if ($_ENV["FRONSKY_API_ENABLED"] !== "true") {
            return 404;
        }

        $errorMsg = "There was an error processing your social login request. Please try again.";
        if (empty($_POST["data"])) {
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg));
            return 200;
        }

        $data = json_decode($_POST["data"], true);
        if (empty($data)) {
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg));
            return 200;
        }

        if (empty($data["id"]) || empty($data["username"]) || empty($data["email"]) || empty($data["full_name"]) || !isset($data["is_admin"])) {
            PageUtil::redirect(self::LOGIN_PATH, array("ERROR" => $errorMsg));
            return 200;
        }

        AuthUtil::createUserData($data);
        PageUtil::redirect(AuthComponent::getSettings()["login.redirect_to"]);
        return 200;
    }

    public function login(): int
    {
        if ($_ENV["FRONSKY_API_ENABLED"] !== "true") {
            return 404;
        }

        $url = self::API_BASE_URL . "/login/" . $_ENV["FRONSKY_API_KEY"] . "/" . $_ENV["FRONSKY_API_SECRET"];
        if (!PageUtil::doesUrlExist($url)) {
            return 500;
        }
        PageUtil::redirect($url, specificUrl: true);
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

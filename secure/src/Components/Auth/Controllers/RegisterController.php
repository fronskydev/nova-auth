<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\AuthUtil;
use src\Components\Auth\Enums\AuthTypes;
use src\Core\PageInfo;
use src\Core\PageUtil;
use src\Mailer\Mailer;
use src\Models\Authentications;
use src\Models\Users;
use Random\RandomException;

class RegisterController extends ComponentController
{
    private const string REGISTER_PATH = "/register";
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function index(): int
    {
        if (!AuthComponent::getSettings()["register.enabled"]) {
            return 404;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Register";
        $this->pageInfo->headerEnabled = false;
        $this->pageInfo->footerEnabled = false;
        $this->pageInfo->styles = [
            "auth.css"
        ];
        $data = [];
        $this->render("register", $data, $this->pageInfo);
        return 200;
    }

    public function submit(): int
    {
        if (!AuthComponent::getSettings()["register.enabled"]) {
            return 404;
        }

        if (empty($_POST["csrf"]) || $_POST["csrf"] !== $_ENV["CSRF_TOKEN"]) {
            PageUtil::redirect(self::REGISTER_PATH);
            return 200;
        }
        unset($_POST["csrf"]);
        if (!isset($_POST["legalCheck"])) {
            $errorMsg = "Please accept the Terms of Use and Privacy Policy.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (empty($_POST["full_name"]) || empty($_POST["username"]) || empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["confirm_password"])) {
            $errorMsg = "Please fill in all fields.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid email address.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if ($_POST["password"] !== $_POST["confirm_password"]) {
            $errorMsg = "Passwords do not match.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (strlen($_POST["password"]) < AuthComponent::getSettings()["password.min_length"]) {
            $errorMsg = "Password must be at least " . AuthComponent::getSettings()["password.min_length"] . " characters long.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (AuthComponent::getSettings()["password.require_uppercase"] && !preg_match("/[A-Z]/", $_POST["password"])) {
            $errorMsg = "Password must contain at least one uppercase letter.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (AuthComponent::getSettings()["password.require_number"] && !preg_match("/[0-9]/", $_POST["password"])) {
            $errorMsg = "Password must contain at least one number.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (AuthComponent::getSettings()["password.require_special_characters"] && !preg_match("/[!@#$%^&*()_\-+=\[\]{};:<>,.?\/|`~]/", $_POST["password"])) {
            $errorMsg = "Password must contain at least one special character.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $fullName = $_POST["full_name"];
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $usersModel = new Users();
        if ($usersModel->getUserDetails($username) || $usersModel->getUserDetails($email)) {
            $errorMsg = "An error occurred while creating your account. Please try again later.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $emailVerified = 1;
        if (AuthComponent::getSettings()["verify_email.enabled"]) {
            $emailVerified = 0;
        }

        $passwordSalt = generateRandomString(32);
        $password = $password . $passwordSalt;
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $usersModel->create(array(
            "username" => encryptText($username, "_username"),
            "email" => encryptText($email, "_email"),
            "full_name" => encryptText($fullName, "_full_name"),
            "password" => $passwordHash,
            "password_salt" => encryptText($passwordSalt, "_password_salt"),
            "is_active" => 1,
            "is_admin" => 0,
            "is_social" => 0,
            "email_verified" => $emailVerified
        ));

        $usersModel = new Users();
        $user = $usersModel->getUserDetails($email);
        if (!$user) {
            $errorMsg = "An error occurred while creating your account. Please try again later.";
            PageUtil::redirect(self::REGISTER_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $mail = new Mailer();
        $templateData = [
            "title" => "Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])),
            "user_name" => decryptText($user["full_name"], "_full_name"),
            "sender_name" => $_ENV["MAIL_FROM_NAME"],
        ];

        if (AuthComponent::getSettings()["verify_email.enabled"]) {
            $authenticationsModel = new Authentications();
            $authentications = $authenticationsModel->findBy("user_id", $user["id"]);
            foreach ($authentications as $authentication) {
                if ($authentication["type"] === AuthTypes::EMAIL_VERIFICATION->name) {
                    $authenticationsModel->delete($authentication["id"]);
                }
            }

            $uniqueIdentifier = $authenticationsModel->generateUniqueIdentifier();

            $authenticationsModel->create([
                "user_id" => $user["id"],
                "unique_identifier" => encryptText($uniqueIdentifier, "_unique_identifier"),
                "type" => AuthTypes::EMAIL_VERIFICATION->name
            ]);

            $templateFile = MAILER_DIR . "/register-with-verify.html";
            $templateData["action_url"] = PUBLIC_URL . "/verify-email/" . $uniqueIdentifier;
            $templateData["action_text"] = "Verify Email";
        } else {
            $templateFile = MAILER_DIR . "/register.html";
        }

        $mail->setHtmlTemplate($templateFile, $templateData)
            ->addSubject("Welcome to " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])))
            ->addRecipient(decryptText($user["email"], "_email"))
            ->setReplyTo($_ENV["MAIL_FROM_ADDRESS"], $_ENV["MAIL_FROM_NAME"])
            ->send();

        $successMsg = "Your account has been created successfully. You can now log in.";
        if (AuthComponent::getSettings()["verify_email.enabled"]) {
            $successMsg = "Your account has been created successfully. We’ve sent a verification email to your inbox. Please confirm your email address before logging in. If you don’t see the email, check your spam folder.";
        }

        PageUtil::redirect(AuthComponent::getSettings()["register.redirect_to"], array("SUCCESS" => $successMsg));
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\Enums\AuthTypes;
use src\Core\PageInfo;
use src\Core\PageUtil;
use src\Mailer\Mailer;
use src\Models\Authentications;
use src\Models\Users;

class ForgotPasswordController extends ComponentController
{
    private const string FORGOT_PASSWORD_PATH = "/forgot-password";
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function index(): int
    {
        if (!AuthComponent::getSettings()["reset_password.enabled"]) {
            return 404;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Forgot Password";
        $this->pageInfo->headerEnabled = false;
        $this->pageInfo->footerEnabled = false;
        $this->pageInfo->styles = [
            "auth.css"
        ];
        $data = [];
        $this->render("forgot-password", $data, $this->pageInfo);
        return 200;
    }

    public function submit(): int
    {
        if (!AuthComponent::getSettings()["reset_password.enabled"]) {
            return 404;
        }

        if (empty($_POST["csrf"]) || $_POST["csrf"] !== $_ENV["CSRF_TOKEN"]) {
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH);
            return 200;
        }
        if (empty($_POST["email"])) {
            $errorMsg = "Please enter your email address.";
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid email address.";
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH, array("ERROR" => $errorMsg, "data" => json_encode($_POST)));
            return 200;
        }

        $email = $_POST["email"];
        $usersModel = new Users();
        $user = $usersModel->getUserDetails($email);
        $successMsg = "If the email you entered is associated with an account, you will receive an email with instructions on how to reset your password.";
        if (!$user || $user["is_active"] == 0 || $user["is_social"] == 1) {
            PageUtil::redirect(self::FORGOT_PASSWORD_PATH, array("SUCCESS" => $successMsg));
            return 200;
        }

        $authenticationsModel = new Authentications();
        $authentications = $authenticationsModel->query("SELECT * FROM authentications WHERE user_id = '" . $user["id"] . "' AND type = '" . AuthTypes::PASSWORD_RESET->name . "';");
        if ($authentications) {
            foreach ($authentications as $authentication) {
                $authenticationsModel->delete($authentication["id"]);
            }
        }

        $uniqueIdentifier = $authenticationsModel->generateUniqueIdentifier();
        $authenticationsModel->create([
            "user_id" => $user["id"],
            "unique_identifier" => $uniqueIdentifier,
            "type" => AuthTypes::PASSWORD_RESET->name
        ]);

        $mail = new Mailer();
        $mail->setHtmlTemplate(MAILER_DIR . "/reset-password.html", [
            "title" => "Reset Your Password",
            "user_name" => $user["full_name"],
            "sender_name" => $_ENV["MAIL_FROM_NAME"],
            "action_url" => PUBLIC_URL . "/reset-password/" . $uniqueIdentifier,
            "action_text" => "Reset Password"
        ])
            ->addSubject("Reset Your Password")
            ->addRecipient($user["email"])
            ->send();

        PageUtil::redirect(self::FORGOT_PASSWORD_PATH, array("SUCCESS" => $successMsg));
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

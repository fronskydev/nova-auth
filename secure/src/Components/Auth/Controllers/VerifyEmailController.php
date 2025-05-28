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

class VerifyEmailController extends ComponentController
{
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function index($args): int
    {
        if (!AuthComponent::getSettings()["verify_email.enabled"]) {
            return 404;
        }
        if (empty($args)) {
            return 404;
        }

        $uniqueIdentifier = $args[0];
        $authenticationsModel = new Authentications();
        $authentication = $authenticationsModel->query("SELECT * FROM authentications WHERE unique_identifier = '" . $uniqueIdentifier . "' AND type = '" . AuthTypes::EMAIL_VERIFICATION->name . "';");
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
                return 404;
            }

            $uniqueIdentifier = $authenticationsModel->generateUniqueIdentifier();
            $authenticationsModel->delete($authentication["id"]);
            $authenticationsModel->create([
                "user_id" => $user["id"],
                "type" => AuthTypes::EMAIL_VERIFICATION->name,
                "unique_identifier" => $uniqueIdentifier
            ]);

            $mail = new Mailer();
            $mail->setHtmlTemplate(MAILER_DIR . "/verify-email.html", [
                "title" => "Email Verification Required",
                "user_name" => $user["full_name"],
                "sender_name" => $_ENV["MAIL_FROM_NAME"],
                "action_url" => PUBLIC_URL . "/verify-email/" . $uniqueIdentifier,
                "action_text" => "Verify Email"
            ])
                ->addSubject("Email Verification Required")
                ->addRecipient($user["email"])
                ->send();

            $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Verify Email Expired";
            $this->pageInfo->headerEnabled = false;
            $this->pageInfo->footerEnabled = false;
            $this->pageInfo->styles = [
                "auth.css"
            ];
            $data = [];
            $this->render("verify-email-expired", $data, $this->pageInfo);
            return 200;
        }

        $usersModel->update($authentication["user_id"], ["email_verified" => 1]);
        $authenticationsModel->delete($authentication["id"]);
        PageUtil::redirect("/login", array("SUCCESS" => "Your email has been verified. You can now log in."));
        return 200;
    }

    public function resendVerificationEmail($args): int
    {
        if (empty($args)) {
            return 404;
        }

        $email = decryptText($args[0]);
        if (empty($email)) {
            return 404;
        }

        $usersModel = new Users();
        $user = $usersModel->getUserDetails($email);
        if (!$user) {
            return 404;
        }

        if ($user["email_verified"]) {
            PageUtil::redirect("/login", array("SUCCESS" => "Your email is already verified. You can now log in."));
            return 200;
        }

        if (!AuthComponent::getSettings()["verify_email.enabled"]) {
            $usersModel->update($user["id"], ["email_verified" => 1]);
            PageUtil::redirect("/login", array("SUCCESS" => "Your email has been verified. You can now log in."));
            return 200;
        }

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
            "unique_identifier" => $uniqueIdentifier,
            "type" => AuthTypes::EMAIL_VERIFICATION->name
        ]);

        $mail = new Mailer();
        $title = "Verify your email for your " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " account";
        $templateData = [
            "title" => $title,
            "user_name" => $user["full_name"],
            "sender_name" => $_ENV["MAIL_FROM_NAME"],
            "action_url" => PUBLIC_URL . "/verify-email/" . $uniqueIdentifier,
            "action_text" => "Verify Email"
        ];

        $mail->setHtmlTemplate(MAILER_DIR . "/verify-email.html", $templateData)
            ->addSubject($title)
            ->addRecipient($user["email"])
            ->setReplyTo($_ENV["MAIL_FROM_ADDRESS"], $_ENV["MAIL_FROM_NAME"])
            ->send();

        PageUtil::redirect("/login", array("SUCCESS" => "A new verification email has been sent to your email address. Please check your inbox."));

        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Components\Auth\AuthUtil;
use src\Core\PageUtil;

class LogoutController extends ComponentController
{
    public function index(): int
    {
        AuthUtil::logoutClient();
        PageUtil::redirect(AuthComponent::getSettings()["logout.redirect_to"]);
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

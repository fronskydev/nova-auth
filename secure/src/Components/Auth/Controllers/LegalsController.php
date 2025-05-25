<?php

namespace src\Components\Auth\Controllers;

use src\Abstracts\ComponentController;
use src\Components\Auth\AuthComponent;
use src\Core\PageInfo;

class LegalsController extends ComponentController
{
    private PageInfo $pageInfo;

    public function __construct()
    {
        $this->pageInfo = new PageInfo();
    }

    public function terms(): int
    {
        if (!AuthComponent::getSettings()["register.enabled"]) {
            return 404;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Terms of Use";
        $data = ["app" => ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"]))];
        $this->render("legals/terms", $data, $this->pageInfo);
        return 200;
    }

    public function privacy(): int
    {
        if (!AuthComponent::getSettings()["register.enabled"]) {
            return 404;
        }

        $this->pageInfo->title = ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) . " | Privacy Policy";
        $data = ["app" => ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"]))];
        $this->render("legals/privacy", $data, $this->pageInfo);
        return 200;
    }

    protected function getComponentName(): string
    {
        return "Auth";
    }
}

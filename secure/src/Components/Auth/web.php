<?php

return [
    // Legals
    "/terms" => ["method" => "GET", "action" => "LegalsController@terms"],
    "/privacy" => ["method" => "GET", "action" => "LegalsController@privacy"],

    // Auth
    "/login" => [
        "method" => ["GET", "POST"],
        "action" => "LoginController@index",
        "middleware" => "NoAuthMiddleware"
    ], "/login/submit" => ["method" => "POST", "action" => "LoginController@submit", "middleware" => "NoAuthMiddleware"],

    "/register" => [
        "method" => ["GET", "POST"],
        "action" => "RegisterController@index",
        "middleware" => "NoAuthMiddleware"
    ], "/register/submit" => ["method" => "POST", "action" => "RegisterController@submit", "middleware" => "NoAuthMiddleware"],

    "/forgot-password" => [
        "method" => ["GET", "POST"],
        "action" => "ForgotPasswordController@index",
        "middleware" => "NoAuthMiddleware"
    ], "/forgot-password/submit" => ["method" => "POST", "action" => "ForgotPasswordController@submit", "middleware" => "NoAuthMiddleware"],

    "/reset-password" => [
        "method" => ["GET", "POST"],
        "action" => "ResetPasswordController@index",
        "middleware" => ["NoAuthMiddleware", "ResetPasswordMiddleware"]
    ], "/reset-password/submit" => ["method" => "POST", "action" => "ResetPasswordController@submit", "middleware" => "NoAuthMiddleware"],

    "/verify-email" => [
        "method" => "GET",
        "action" => "VerifyEmailController@index",
        "middleware" => "VerifyEmailMiddleware"
    ],

    "/api" => [
        "method" => "POST",
        "action" => "ApiController@index",
        "middleware" => "NoAuthMiddleware"
    ],

    "/api/login" => [
        "method" => "GET",
        "action" => "ApiController@login",
        "middleware" => "NoAuthMiddleware"
    ],

    "/api/submit" => [
        "method" => "POST",
        "action" => "ApiController@apiSubmit",
        "middleware" => "NoAuthMiddleware"
    ],

    "/logout" => [
        "method" => "GET",
        "action" => "LogoutController@index",
        "middleware" => "AuthMiddleware"
    ],
];

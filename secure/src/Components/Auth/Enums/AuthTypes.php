<?php

namespace src\Components\Auth\Enums;

enum AuthTypes
{
    case EMAIL_VERIFICATION;
    case PASSWORD_RESET;
}

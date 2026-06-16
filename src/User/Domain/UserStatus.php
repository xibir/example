<?php

namespace App\User\Domain;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case Inactive = 'inactive';
}

<?php

namespace App\Factory;

use App\Entity\User;

class UserFactory
{
    public function create(): User
    {
        return new User();
    }
}

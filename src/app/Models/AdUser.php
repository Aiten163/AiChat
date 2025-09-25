<?php

namespace App\Models;

use LdapRecord\Models\ActiveDirectory\Group as BaseGroup;

class AdUser extends BaseGroup
{
    protected string|null $connection = 'default';
}

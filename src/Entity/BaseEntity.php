<?php

namespace App\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;

abstract class BaseEntity
{
    use TimestampableEntity;
}
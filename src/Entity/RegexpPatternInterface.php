<?php

namespace App\Entity;

interface RegexpPatternInterface
{
    public function buildRegexpPattern(): string;
}
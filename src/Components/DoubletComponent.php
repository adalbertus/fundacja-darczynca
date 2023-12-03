<?php

namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

// https://symfony.com/bundles/ux-twig-component/current/index.html#creating-a-basic-component

#[AsTwigComponent('doublet')]
class DoubletComponent
{
    public string $first = '';
    public string $first_html = '';
    public string $first_tooltip = '';
    public string $first_css = 'text-bg-secondary';
    public string $last = '';
    public string $last_html = '';
    public string $last_tooltip = '';
    public string $last_css = 'text-bg-info';
}
<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Enum case order = sidebar order. Do NOT reorder without intention.
 */
enum NavigationGroupEnum: string implements HasLabel
{
    case Sales          = 'Sales';
    case Customers      = 'Customers';
    case Catalogue      = 'Catalogue';
    case Operations     = 'Operations';
    case Finance        = 'Finance';
    case Analytics      = 'Analytics';
    case Compliance     = 'Compliance';
    case HumanResources = 'Human Resources';
    case Content        = 'Content';
    case Settings       = 'Settings';

    public function getLabel(): string
    {
        return $this->value;
    }
}

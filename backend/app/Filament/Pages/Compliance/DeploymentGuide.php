<?php
namespace App\Filament\Pages\Compliance;

use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class DeploymentGuide extends Page
{
    protected string $view = 'filament.pages.compliance.deployment-guide';
    public static function getNavigationIcon(): string  { return 'heroicon-o-rocket-launch'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int     { return 99; }
    public function getTitle(): string                  { return 'Go Live — Deploy'; }
}

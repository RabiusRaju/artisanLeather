<?php
namespace App\Filament\Pages\Compliance;

use Filament\Pages\Page;

class DeploymentGuide extends Page
{
    protected string $view = 'filament.pages.compliance.deployment-guide';
    public static function getNavigationIcon(): string  { return 'heroicon-o-rocket-launch'; }
    public static function getNavigationGroup(): string { return 'Compliance'; }
    public static function getNavigationSort(): int     { return 10; }
    public function getTitle(): string                  { return 'Go Live — Deploy'; }
}

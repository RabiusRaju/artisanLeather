<?php
namespace App\Filament\Resources\Surveys\Pages;
use App\Filament\Resources\Surveys\SurveyResource;


use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditSurvey extends EditRecord {
    protected static string $resource = SurveyResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}

<?php
namespace App\Filament\Resources\Surveys\Pages;
use App\Filament\Resources\Surveys\SurveyResource;

use Filament\Resources\Pages\CreateRecord;
class CreateSurvey extends CreateRecord {
    protected static string $resource = SurveyResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]); }
}


<?php
namespace App\Filament\Resources\Blog\Pages;
use App\Filament\Resources\Blog\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditPost extends EditRecord {
    protected static string $resource = PostResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}

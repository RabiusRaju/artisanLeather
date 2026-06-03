<?php
namespace App\Filament\Resources\Blog\Pages;
use App\Filament\Resources\Blog\PostResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePost extends CreateRecord {
    protected static string $resource = PostResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]); }
}

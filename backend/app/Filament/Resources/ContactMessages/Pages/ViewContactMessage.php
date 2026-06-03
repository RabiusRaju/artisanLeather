<?php
namespace App\Filament\Resources\ContactMessages\Pages;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewContactMessage extends ViewRecord {
    protected static string $resource = ContactMessageResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }

    protected function mutateFormDataBeforeFill(array $data): array {
        // Mark as read when viewed
        $this->record->update(['status' => $this->record->status === 'unread' ? 'read' : $this->record->status]);
        return $data;
    }
}

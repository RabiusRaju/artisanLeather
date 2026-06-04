<?php

namespace App\Filament\Resources\HR\Employees\Pages;

use App\Filament\Resources\HR\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }

    // Pre-fill System Access tab when opening an existing employee
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Employee $record */
        $record = $this->getRecord();

        if ($record->user_id) {
            $user = User::find($record->user_id);
            $data['can_login']    = true;
            $data['login_email']  = $user?->email ?? '';
            // Never pre-fill password for security
        } else {
            $data['can_login'] = false;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Employee $record */
        $record   = $this->record;
        $state    = $this->data;
        $canLogin = $state['can_login']   ?? false;
        $email    = trim($state['login_email'] ?? '') ?: $record->email;
        $password = $state['login_password'] ?? null;

        if ($canLogin && $email) {
            if ($record->user_id) {
                // Update existing user
                $user = User::find($record->user_id);
                if ($user) {
                    $user->update([
                        'name'  => $record->name,
                        'email' => $email,
                        'phone' => $record->phone,
                    ]);
                    if ($password) {
                        $user->update(['password' => Hash::make($password)]);
                    }
                }
            } else {
                // Create new user and link
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => $record->name,
                        'email'    => $email,
                        'phone'    => $record->phone,
                        'password' => Hash::make($password ?: Str::random(16)),
                    ]
                );
                if ($password) {
                    $user->update(['password' => Hash::make($password)]);
                }
                $record->updateQuietly(['user_id' => $user->id]);
            }
        } elseif (!$canLogin && $record->user_id) {
            // Revoke access — delete the user account
            User::find($record->user_id)?->delete();
            $record->updateQuietly(['user_id' => null]);
        }
    }
}

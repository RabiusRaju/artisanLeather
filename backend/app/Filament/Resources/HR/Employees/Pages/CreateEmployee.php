<?php

namespace App\Filament\Resources\HR\Employees\Pages;

use App\Filament\Resources\HR\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()->label('New Employee')];
    }

    protected function afterCreate(): void
    {
        $state    = $this->data;
        $canLogin = $state['can_login']      ?? false;
        $email    = trim($state['login_email'] ?? '') ?: $this->record->email;
        $password = $state['login_password'] ?? null;

        if (!$canLogin || !$email) return;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $this->record->name,
                'email'    => $email,
                'phone'    => $this->record->phone,
                'password' => Hash::make($password ?: Str::random(16)),
            ]
        );

        if ($password) {
            $user->update(['password' => Hash::make($password)]);
        }

        $this->record->updateQuietly(['user_id' => $user->id]);
    }
}

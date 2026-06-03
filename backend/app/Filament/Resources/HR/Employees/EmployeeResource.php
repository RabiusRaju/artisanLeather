<?php
namespace App\Filament\Resources\HR\Employees;

use App\Filament\Resources\HR\Employees\Pages;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-users'; }
    public static function getNavigationGroup(): string { return 'Human Resources'; }
    public static function getNavigationSort(): int     { return 2; }
    public static function getNavigationBadge(): ?string
    {
        $count = Employee::where('status', 'active')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'success'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Employee')->tabs([

                // ── 1. Personal ──────────────────────────────────────────
                Tab::make('Personal')->icon('heroicon-o-user')->schema([
                    Section::make('Identity')->schema([
                        FileUpload::make('photo')
                            ->image()->imageEditor()->imageEditorMode(3)
                            ->imageEditorAspectRatioOptions(['1:1',null])
                            ->directory('employees')->disk('public')
                            ->maxSize(2048)->columnSpanFull(),

                        TextInput::make('name')->required()->columnSpan(2),
                        TextInput::make('name_ar')->label('Name (Arabic)')->columnSpan(1),

                        Select::make('gender')->options(['male'=>'Male','female'=>'Female'])->columnSpan(1),
                        DatePicker::make('date_of_birth')->label('Date of Birth')->columnSpan(1),
                        TextInput::make('nationality')->default('Omani')->columnSpan(1),
                    ])->columns(3),

                    Section::make('Documents & Visa')->schema([
                        TextInput::make('national_id')->label('National / Civil ID')->columnSpan(1),
                        TextInput::make('passport_number')->columnSpan(1),
                        Select::make('visa_type')->label('Visa / Residency Type')->options([
                            'citizen'          => '🇴🇲 Omani Citizen',
                            'employment_visa'  => '📋 Employment Visa',
                            'dependent_visa'   => '👨‍👩‍👧 Dependent Visa',
                            'visit_visa'       => '✈️ Visit Visa',
                            'other'            => 'Other',
                        ])->nullable()->columnSpan(1),
                        DatePicker::make('visa_expiry')->label('Visa Expiry Date')->columnSpan(1),
                    ])->columns(2),
                ]),

                // ── 2. Employment ─────────────────────────────────────────
                Tab::make('Employment')->icon('heroicon-o-briefcase')->schema([
                    Section::make('Role & Department')->schema([
                        Select::make('department_id')->label('Department')
                            ->options(Department::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()->required()->columnSpan(1),

                        TextInput::make('job_title')->label('Job Title')
                            ->placeholder('e.g. Head Craftsman, Artisan, Store Manager')
                            ->required()->columnSpan(2),

                        Select::make('employment_type')->label('Employment Type')->options([
                            'full_time'  => '⏰ Full-time',
                            'part_time'  => '🕐 Part-time',
                            'contract'   => '📝 Contract',
                            'freelance'  => '🔓 Freelance',
                        ])->default('full_time')->required()->columnSpan(1),

                        Select::make('status')->options([
                            'active'      => '🟢 Active',
                            'probation'   => '🟡 Probation',
                            'on_leave'    => '🟠 On Leave',
                            'terminated'  => '🔴 Terminated',
                        ])->default('active')->required()->columnSpan(1),
                    ])->columns(3),

                    Section::make('Dates & Compensation')->schema([
                        DatePicker::make('date_hired')->label('Date Hired')->required()->columnSpan(1),
                        DatePicker::make('date_terminated')->label('Termination Date')->columnSpan(1),
                        TextInput::make('monthly_salary_omr')->label('Monthly Salary (OMR)')
                            ->numeric()->prefix('OMR')->step(0.001)
                            ->helperText('For salaried employees')->columnSpan(1),
                        TextInput::make('hourly_rate_omr')->label('Hourly Rate (OMR)')
                            ->numeric()->prefix('OMR')->step(0.001)
                            ->helperText('For hourly / craftsmen')->columnSpan(1),
                    ])->columns(2),
                ]),

                // ── 3. Contact ────────────────────────────────────────────
                Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    Section::make('Contact Information')->schema([
                        TextInput::make('phone')->required()->columnSpan(1),
                        TextInput::make('whatsapp')->columnSpan(1),
                        TextInput::make('email')->email()->columnSpan(1),
                        Select::make('governorate')->options([
                            'Muscat'=>'Muscat','Dhofar'=>'Dhofar','Musandam'=>'Musandam',
                            'Al Buraimi'=>'Al Buraimi','Al Batinah North'=>'Al Batinah North',
                            'Al Batinah South'=>'Al Batinah South','Al Dakhliyah'=>'Al Dakhliyah',
                            'Al Dhahirah'=>'Al Dhahirah','Al Sharqiyah North'=>'Al Sharqiyah North',
                            'Al Sharqiyah South'=>'Al Sharqiyah South','Al Wusta'=>'Al Wusta',
                        ])->searchable()->columnSpan(1),
                        TextInput::make('city')->columnSpan(1),
                        Textarea::make('address')->rows(2)->columnSpanFull(),
                    ])->columns(3),

                    Section::make('Emergency Contact')->schema([
                        TextInput::make('emergency_contact_name')->label('Name')->columnSpan(1),
                        TextInput::make('emergency_contact_phone')->label('Phone')->columnSpan(1),
                    ])->columns(2),
                ]),

                // ── 4. Skills & Notes ─────────────────────────────────────
                Tab::make('Skills')->icon('heroicon-o-star')->schema([
                    Section::make('Leather Craft Skills')->schema([
                        TagsInput::make('skills')
                            ->label('Skills')
                            ->suggestions([
                                'cutting', 'stitching', 'hand_stitching', 'machine_stitching',
                                'burnishing', 'dyeing', 'edge_finishing', 'hardware_fitting',
                                'monogramming', 'design', 'pattern_making', 'quality_control',
                                'packaging', 'customer_service', 'sales', 'inventory',
                            ])
                            ->helperText('Press Enter to add each skill')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Private Admin Notes')
                            ->rows(5)
                            ->placeholder('Any important notes about this employee...')
                            ->columnSpanFull(),
                    ]),
                ]),

                // ── 5. System Access ──────────────────────────────────────
                Tab::make('System Access')->icon('heroicon-o-lock-closed')->schema([
                    Section::make('Admin Panel Login')
                        ->description('If enabled, this employee can login to the admin panel at ' . config('app.url') . '/admin')
                        ->schema([
                            Toggle::make('can_login')
                                ->label('Allow this employee to access the admin panel')
                                ->helperText('Creates a system login account for this employee.')
                                ->live()
                                ->dehydrated(false)
                                ->default(false),

                            TextInput::make('login_email')
                                ->label('Login Email Address')
                                ->email()
                                ->placeholder('Will default to employee email if empty')
                                ->helperText('The email address used to login.')
                                ->dehydrated(false)
                                ->visible(fn($get) => $get('can_login'))
                                ->columnSpan(1),

                            TextInput::make('login_password')
                                ->label('Password')
                                ->password()
                                ->minLength(8)
                                ->placeholder('Minimum 8 characters')
                                ->helperText('Leave blank on edit to keep the current password unchanged.')
                                ->dehydrated(false)
                                ->visible(fn($get) => $get('can_login'))
                                ->required(fn($get, $livewire) => $get('can_login') && $livewire instanceof Pages\CreateEmployee)
                                ->columnSpan(1),

                            // Show current access status when editing
                            Placeholder::make('current_access')
                                ->label('Current Status')
                                ->content(function ($record) {
                                    if (!$record) return new HtmlString('<span style="color:#9ca3af;font-size:0.85rem;">Not yet created.</span>');
                                    if ($record->user_id) {
                                        $user = User::find($record->user_id);
                                        return new HtmlString(
                                            '<span style="color:#22c55e;font-weight:600;">🔑 Admin access ACTIVE</span>'
                                            . '<br><span style="color:#9ca3af;font-size:0.8rem;">Login email: ' . e($user?->email ?? '—') . '</span>'
                                        );
                                    }
                                    return new HtmlString('<span style="color:#9ca3af;font-size:0.85rem;">🔒 No admin access</span>');
                                })
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make()->schema([
                \Filament\Schemas\Components\Grid::make(4)->schema([
                    \Filament\Infolists\Components\ImageEntry::make('photo')
                        ->disk('public')->square()->height(80),
                    \Filament\Infolists\Components\TextEntry::make('name')->weight('bold')->size('lg'),
                    \Filament\Infolists\Components\TextEntry::make('job_title')
                        ->label('Job Title')->color('warning'),
                    \Filament\Infolists\Components\TextEntry::make('status')->badge()
                        ->color(fn($state) => match($state) {
                            'active'     => 'success', 'probation' => 'warning',
                            'on_leave'   => 'info',    'terminated' => 'danger', default => 'gray',
                        }),
                ]),
            ])->compact(),

            \Filament\Schemas\Components\Grid::make(3)->schema([
                \Filament\Schemas\Components\Section::make('Employment')->schema([
                    \Filament\Infolists\Components\TextEntry::make('department.name')->label('Department'),
                    \Filament\Infolists\Components\TextEntry::make('employment_type')->label('Type')
                        ->formatStateUsing(fn($s) => match($s) {
                            'full_time'=>'Full-time','part_time'=>'Part-time',
                            'contract'=>'Contract','freelance'=>'Freelance',default=>$s
                        }),
                    \Filament\Infolists\Components\TextEntry::make('date_hired')->label('Hired')->date('d M Y'),
                    \Filament\Infolists\Components\TextEntry::make('years_of_service')
                        ->label('Years of Service')
                        ->getStateUsing(fn(Employee $r) => $r->years_of_service . ' years'),
                ])->columnSpan(1),

                \Filament\Schemas\Components\Section::make('Contact')->schema([
                    \Filament\Infolists\Components\TextEntry::make('phone'),
                    \Filament\Infolists\Components\TextEntry::make('whatsapp')->placeholder('—'),
                    \Filament\Infolists\Components\TextEntry::make('email')->placeholder('—'),
                    \Filament\Infolists\Components\TextEntry::make('governorate')->placeholder('—'),
                ])->columnSpan(1),

                \Filament\Schemas\Components\Section::make('Compensation')->schema([
                    \Filament\Infolists\Components\TextEntry::make('monthly_salary_omr')
                        ->label('Monthly Salary')
                        ->getStateUsing(fn(Employee $r) => $r->monthly_salary_omr ? 'OMR ' . number_format($r->monthly_salary_omr, 3) : '—'),
                    \Filament\Infolists\Components\TextEntry::make('hourly_rate_omr')
                        ->label('Hourly Rate')
                        ->getStateUsing(fn(Employee $r) => $r->hourly_rate_omr ? 'OMR ' . number_format($r->hourly_rate_omr, 3) . '/hr' : '—'),
                    \Filament\Infolists\Components\TextEntry::make('nationality'),
                    \Filament\Infolists\Components\TextEntry::make('visa_type')
                        ->label('Visa')->placeholder('Citizen')->formatStateUsing(fn($s) => match($s) {
                            'employment_visa'=>'Employment Visa','dependent_visa'=>'Dependent Visa',
                            'visit_visa'=>'Visit Visa','citizen'=>'Omani Citizen',default=>$s??'Citizen'
                        }),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->disk('public')->square()->imageSize(44)->circular()->defaultImageUrl(fn() => null),
                TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn(Employee $r) => $r->job_title),
                TextColumn::make('department.name')->badge()->color('info'),
                TextColumn::make('nationality')->badge()
                    ->color(fn($state) => $state === 'Omani' ? 'success' : 'warning'),
                TextColumn::make('phone'),
                TextColumn::make('employment_type')->badge()
                    ->color(fn($s) => match($s) {
                        'full_time'=>'success','part_time'=>'info','contract'=>'warning','freelance'=>'gray',default=>'gray'
                    })
                    ->formatStateUsing(fn($s) => match($s) {
                        'full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','freelance'=>'Freelance',default=>$s
                    }),
                TextColumn::make('status')->badge()
                    ->color(fn($s) => match($s) {
                        'active'=>'success','probation'=>'warning','on_leave'=>'info','terminated'=>'danger',default=>'gray'
                    }),
                TextColumn::make('date_hired')->date('d M Y')->label('Hired')->sortable(),
                IconColumn::make('user_id')
                    ->label('Admin Access')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-open')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Employee $r) => $r->user_id ? 'Has admin panel access' : 'No admin access'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('department_id')->label('Department')
                    ->options(Department::pluck('name','id')),
                SelectFilter::make('status')->options([
                    'active'=>'Active','probation'=>'Probation','on_leave'=>'On Leave','terminated'=>'Terminated',
                ]),
                SelectFilter::make('nationality'),
                SelectFilter::make('employment_type')->options([
                    'full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','freelance'=>'Freelance',
                ]),
                TernaryFilter::make('user_id')
                    ->label('Admin Access')
                    ->placeholder('All employees')
                    ->trueLabel('Has admin access')
                    ->falseLabel('No admin access')
                    ->nullable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view'   => Pages\ViewEmployee::route('/{record}'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}

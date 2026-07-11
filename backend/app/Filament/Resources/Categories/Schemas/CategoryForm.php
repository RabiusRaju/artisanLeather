<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('name_ar')->default(null),
                        TextInput::make('slug')->required(),
                        Toggle::make('is_active')->required(),
                        TextInput::make('sort_order')->required()->numeric()->default(0),
                    ]),

                Section::make('Homepage Collection Card Image')
                    ->description('This image appears on the homepage Collections cards.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Upload Image')
                            ->image()
                            ->imageEditor()
                            ->directory('categories')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(4096),
                        TextInput::make('image_alt')
                            ->label('Image ALT Text')
                            ->maxLength(125),
                    ]),
            ]);
    }
}

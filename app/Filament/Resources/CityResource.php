<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Cities';

    protected static ?string $modelLabel = 'City';

    protected static ?string $pluralModelLabel = 'Cities';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('City Information')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(City::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\RichEditor::make('overview')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('population')
                            ->numeric()
                            ->label('Population'),

                        Forms\Components\TextInput::make('climate')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('internet_speed_mbps')
                            ->numeric()
                            ->label('Internet Speed (Mbps)'),

                        Forms\Components\TextInput::make('safety_score')
                            ->numeric()
                            ->label('Safety Score (1-10)')
                            ->minValue(1)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('cost_of_living_index')
                            ->numeric()
                            ->label('Cost of Living Index'),

                        Forms\Components\TextInput::make('best_time_to_visit')
                            ->maxLength(255),

                        Forms\Components\TagsInput::make('highlights')
                            ->placeholder('Add highlights...'),

                        Forms\Components\TagsInput::make('images')
                            ->placeholder('Add image URLs...'),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured City'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable(),

                Tables\Columns\TextColumn::make('population')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '-'),

                Tables\Columns\TextColumn::make('internet_speed_mbps')
                    ->label('Internet Speed')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.' Mbps' : '-'),

                Tables\Columns\TextColumn::make('safety_score')
                    ->label('Safety Score')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.'/10' : '-'),

                Tables\Columns\TextColumn::make('cost_of_living_index')
                    ->label('Cost Index')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Cities'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Cities'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}

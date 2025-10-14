<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NeighborhoodResource\Pages;
use App\Models\Neighborhood;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NeighborhoodResource extends Resource
{
    protected static ?string $model = Neighborhood::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Neighborhoods';

    protected static ?string $modelLabel = 'Neighborhood';

    protected static ?string $pluralModelLabel = 'Neighborhoods';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Neighborhood Information')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('City')
                            ->relationship('city', 'name')
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
                            ->unique(Neighborhood::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\Select::make('type')
                            ->options([
                                'business' => 'Business',
                                'residential' => 'Residential',
                                'historic' => 'Historic',
                                'tourist' => 'Tourist',
                                'mixed' => 'Mixed',
                            ]),

                        Forms\Components\Select::make('cost_level')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ]),

                        Forms\Components\TextInput::make('safety_score')
                            ->numeric()
                            ->label('Safety Score (1-10)')
                            ->minValue(1)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('internet_speed_mbps')
                            ->numeric()
                            ->label('Internet Speed (Mbps)'),

                        Forms\Components\TagsInput::make('amenities')
                            ->placeholder('Add amenities...'),

                        Forms\Components\TagsInput::make('transportation')
                            ->placeholder('Add transportation options...'),

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

                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'business',
                        'success' => 'residential',
                        'warning' => 'historic',
                        'info' => 'tourist',
                        'secondary' => 'mixed',
                    ]),

                Tables\Columns\BadgeColumn::make('cost_level')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ]),

                Tables\Columns\TextColumn::make('safety_score')
                    ->label('Safety Score')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.'/10' : '-'),

                Tables\Columns\TextColumn::make('internet_speed_mbps')
                    ->label('Internet Speed')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.' Mbps' : '-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'business' => 'Business',
                        'residential' => 'Residential',
                        'historic' => 'Historic',
                        'tourist' => 'Tourist',
                        'mixed' => 'Mixed',
                    ]),

                Tables\Filters\SelectFilter::make('cost_level')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Neighborhoods'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('city_id', 'asc');
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
            'index' => Pages\ListNeighborhoods::route('/'),
            'create' => Pages\CreateNeighborhood::route('/create'),
            'edit' => Pages\EditNeighborhood::route('/{record}/edit'),
        ];
    }
}

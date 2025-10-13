<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoworkingSpaceResource\Pages;
use App\Filament\Resources\CoworkingSpaceResource\RelationManagers;
use App\Models\CoworkingSpace;
use App\Models\City;
use App\Models\Neighborhood;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoworkingSpaceResource extends Resource
{
    protected static ?string $model = CoworkingSpace::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Coworking Spaces';

    protected static ?string $modelLabel = 'Coworking Space';

    protected static ?string $pluralModelLabel = 'Coworking Spaces';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Space Information')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('City')
                            ->relationship('city', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        
                        Forms\Components\Select::make('neighborhood_id')
                            ->label('Neighborhood')
                            ->relationship('neighborhood', 'name')
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
                            ->unique(CoworkingSpace::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        
                        Forms\Components\TextInput::make('address'),
                        
                        Forms\Components\TextInput::make('website')
                            ->url(),
                        
                        Forms\Components\TextInput::make('phone'),
                        
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'coworking' => 'Coworking Space',
                                'cafe' => 'Cafe',
                                'library' => 'Library',
                                'hotel_lobby' => 'Hotel Lobby',
                                'other' => 'Other',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('wifi_speed_mbps')
                            ->numeric()
                            ->label('WiFi Speed (Mbps)'),
                        
                        Forms\Components\Select::make('wifi_reliability')
                            ->options([
                                'poor' => 'Poor',
                                'fair' => 'Fair',
                                'good' => 'Good',
                                'excellent' => 'Excellent',
                            ]),
                        
                        Forms\Components\Select::make('noise_level')
                            ->options([
                                'quiet' => 'Quiet',
                                'moderate' => 'Moderate',
                                'loud' => 'Loud',
                            ]),
                        
                        Forms\Components\TextInput::make('seating_capacity')
                            ->numeric(),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),
                        
                        Forms\Components\TextInput::make('daily_rate')
                            ->numeric()
                            ->step(0.01)
                            ->label('Daily Rate'),
                        
                        Forms\Components\TextInput::make('monthly_rate')
                            ->numeric()
                            ->step(0.01)
                            ->label('Monthly Rate'),
                        
                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('USD'),
                        
                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->label('Rating (1-5)'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                        
                        Forms\Components\TagsInput::make('amenities')
                            ->placeholder('Add amenities...'),
                        
                        Forms\Components\TagsInput::make('images')
                            ->placeholder('Add image URLs...'),
                        
                        Forms\Components\Toggle::make('has_power_outlets')
                            ->label('Power Outlets'),
                        
                        Forms\Components\Toggle::make('has_air_conditioning')
                            ->label('Air Conditioning'),
                        
                        Forms\Components\Toggle::make('has_kitchen')
                            ->label('Kitchen'),
                        
                        Forms\Components\Toggle::make('has_meeting_rooms')
                            ->label('Meeting Rooms'),
                        
                        Forms\Components\Toggle::make('has_printing')
                            ->label('Printing'),
                        
                        Forms\Components\Toggle::make('is_24_hours')
                            ->label('24 Hours'),
                        
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified'),
                        
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
                
                Tables\Columns\TextColumn::make('neighborhood.name')
                    ->label('Neighborhood')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'coworking',
                        'success' => 'cafe',
                        'warning' => 'library',
                        'info' => 'hotel_lobby',
                        'secondary' => 'other',
                    ]),
                
                Tables\Columns\TextColumn::make('wifi_speed_mbps')
                    ->label('WiFi Speed')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state . ' Mbps' : '-'),
                
                Tables\Columns\BadgeColumn::make('wifi_reliability')
                    ->colors([
                        'danger' => 'poor',
                        'warning' => 'fair',
                        'success' => 'good',
                        'primary' => 'excellent',
                    ]),
                
                Tables\Columns\TextColumn::make('daily_rate')
                    ->label('Daily Rate')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('monthly_rate')
                    ->label('Monthly Rate')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state . '/5' : '-'),
                
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
                
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
                        'coworking' => 'Coworking Space',
                        'cafe' => 'Cafe',
                        'library' => 'Library',
                        'hotel_lobby' => 'Hotel Lobby',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified Spaces'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Spaces'),
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
            'index' => Pages\ListCoworkingSpaces::route('/'),
            'create' => Pages\CreateCoworkingSpace::route('/create'),
            'edit' => Pages\EditCoworkingSpace::route('/{record}/edit'),
        ];
    }
}

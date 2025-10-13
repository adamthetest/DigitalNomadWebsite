<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostItemResource\Pages;
use App\Filament\Resources\CostItemResource\RelationManagers;
use App\Models\CostItem;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CostItemResource extends Resource
{
    protected static ?string $model = CostItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Cost Items';

    protected static ?string $modelLabel = 'Cost Item';

    protected static ?string $pluralModelLabel = 'Cost Items';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cost Information')
                    ->schema([
                        Forms\Components\Select::make('city_id')
                            ->label('City')
                            ->relationship('city', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('category')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., accommodation, food, transport'),
                        
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., 1-bedroom apartment, Street food meal'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of the cost item'),
                        
                        Forms\Components\TextInput::make('price_min')
                            ->numeric()
                            ->step(0.01)
                            ->label('Minimum Price'),
                        
                        Forms\Components\TextInput::make('price_max')
                            ->numeric()
                            ->step(0.01)
                            ->label('Maximum Price'),
                        
                        Forms\Components\TextInput::make('price_average')
                            ->numeric()
                            ->step(0.01)
                            ->label('Average Price'),
                        
                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('USD')
                            ->placeholder('USD, EUR, THB, etc.'),
                        
                        Forms\Components\TextInput::make('unit')
                            ->maxLength(255)
                            ->placeholder('per month, per meal, per night, etc.'),
                        
                        Forms\Components\Select::make('price_range')
                            ->options([
                                'budget' => 'Budget',
                                'mid_range' => 'Mid Range',
                                'luxury' => 'Luxury',
                            ])
                            ->placeholder('Select price range'),
                        
                        Forms\Components\TagsInput::make('details')
                            ->placeholder('Add pricing details...'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Additional notes about pricing'),
                        
                        Forms\Components\DatePicker::make('last_updated')
                            ->label('Last Updated')
                            ->default(now()),
                        
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
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'accommodation',
                        'success' => 'food',
                        'warning' => 'transport',
                        'info' => 'entertainment',
                        'secondary' => 'coworking',
                    ]),
                
                Tables\Columns\TextColumn::make('price_average')
                    ->label('Average Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_min')
                    ->label('Min Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_max')
                    ->label('Max Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('currency')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('price_range')
                    ->colors([
                        'success' => 'budget',
                        'warning' => 'mid_range',
                        'danger' => 'luxury',
                    ]),
                
                Tables\Columns\TextColumn::make('last_updated')
                    ->date()
                    ->sortable(),
                
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
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'accommodation' => 'Accommodation',
                        'food' => 'Food',
                        'transport' => 'Transport',
                        'entertainment' => 'Entertainment',
                        'coworking' => 'Coworking',
                        'utilities' => 'Utilities',
                        'shopping' => 'Shopping',
                    ]),
                
                Tables\Filters\SelectFilter::make('price_range')
                    ->options([
                        'budget' => 'Budget',
                        'mid_range' => 'Mid Range',
                        'luxury' => 'Luxury',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Items'),
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
            'index' => Pages\ListCostItems::route('/'),
            'create' => Pages\CreateCostItem::route('/create'),
            'edit' => Pages\EditCostItem::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Countries';

    protected static ?string $modelLabel = 'Country';

    protected static ?string $pluralModelLabel = 'Countries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Country Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(2)
                            ->label('Country Code (e.g., US, TH)'),
                        
                        Forms\Components\TextInput::make('currency_code')
                            ->required()
                            ->maxLength(3)
                            ->label('Currency Code (e.g., USD, THB)'),
                        
                        Forms\Components\TextInput::make('currency_symbol')
                            ->required()
                            ->maxLength(10)
                            ->label('Currency Symbol (e.g., $, ฿)'),
                        
                        Forms\Components\TextInput::make('timezone')
                            ->required()
                            ->maxLength(255)
                            ->label('Timezone (e.g., Asia/Bangkok)'),
                        
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
                
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Currency')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('currency_symbol')
                    ->label('Symbol')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('timezone')
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Countries'),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}

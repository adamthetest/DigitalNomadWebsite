<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisaRuleResource\Pages;
use App\Models\VisaRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VisaRuleResource extends Resource
{
    protected static ?string $model = VisaRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Visa Rules';

    protected static ?string $modelLabel = 'Visa Rule';

    protected static ?string $pluralModelLabel = 'Visa Rules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visa Information')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label('Destination Country')
                            ->relationship('country', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('nationality')
                            ->required()
                            ->maxLength(2)
                            ->label('Traveler Nationality (Country Code)')
                            ->placeholder('e.g., US, UK, CA'),

                        Forms\Components\Select::make('visa_type')
                            ->options([
                                'visa_free' => 'Visa Free',
                                'visa_on_arrival' => 'Visa on Arrival',
                                'e_visa' => 'E-Visa',
                                'visa_required' => 'Visa Required',
                                'no_entry' => 'No Entry',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('stay_duration_days')
                            ->numeric()
                            ->label('Stay Duration (Days)'),

                        Forms\Components\TextInput::make('validity_days')
                            ->numeric()
                            ->label('Visa Validity (Days)'),

                        Forms\Components\TextInput::make('cost_usd')
                            ->numeric()
                            ->step(0.01)
                            ->label('Cost (USD)'),

                        Forms\Components\Textarea::make('requirements')
                            ->rows(3)
                            ->placeholder('Required documents and conditions'),

                        Forms\Components\Textarea::make('application_process')
                            ->rows(3)
                            ->placeholder('How to apply for the visa'),

                        Forms\Components\TextInput::make('official_website')
                            ->url()
                            ->placeholder('Official government website'),

                        Forms\Components\TagsInput::make('restrictions')
                            ->placeholder('Add restrictions...'),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Additional notes'),

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
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Destination Country')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nationality')
                    ->label('Nationality')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('visa_type')
                    ->colors([
                        'success' => 'visa_free',
                        'warning' => 'visa_on_arrival',
                        'info' => 'e_visa',
                        'danger' => 'visa_required',
                        'secondary' => 'no_entry',
                    ]),

                Tables\Columns\TextColumn::make('stay_duration_days')
                    ->label('Stay Duration')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.' days' : '-'),

                Tables\Columns\TextColumn::make('validity_days')
                    ->label('Validity')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.' days' : '-'),

                Tables\Columns\TextColumn::make('cost_usd')
                    ->label('Cost')
                    ->money('USD')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('visa_type')
                    ->options([
                        'visa_free' => 'Visa Free',
                        'visa_on_arrival' => 'Visa on Arrival',
                        'e_visa' => 'E-Visa',
                        'visa_required' => 'Visa Required',
                        'no_entry' => 'No Entry',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Rules'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('country_id', 'asc');
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
            'index' => Pages\ListVisaRules::route('/'),
            'create' => Pages\CreateVisaRule::route('/create'),
            'edit' => Pages\EditVisaRule::route('/{record}/edit'),
        ];
    }
}

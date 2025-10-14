<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateLinkResource\Pages;
use App\Models\AffiliateLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateLinkResource extends Resource
{
    protected static ?string $model = AffiliateLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Affiliate Links';

    protected static ?string $modelLabel = 'Affiliate Link';

    protected static ?string $pluralModelLabel = 'Affiliate Links';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Affiliate Link Information')
                    ->schema([
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
                            ->unique(AffiliateLink::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('original_url')
                            ->url()
                            ->required()
                            ->label('Original URL'),

                        Forms\Components\TextInput::make('affiliate_url')
                            ->url()
                            ->required()
                            ->label('Affiliate URL'),

                        Forms\Components\TextInput::make('affiliate_provider')
                            ->maxLength(255)
                            ->placeholder('e.g., SafetyWing, Airbnb, Booking.com'),

                        Forms\Components\Select::make('category')
                            ->options([
                                'insurance' => 'Insurance',
                                'accommodation' => 'Accommodation',
                                'transport' => 'Transport',
                                'coworking' => 'Coworking',
                                'food' => 'Food',
                                'entertainment' => 'Entertainment',
                                'shopping' => 'Shopping',
                                'utilities' => 'Utilities',
                            ]),

                        Forms\Components\Select::make('commission_type')
                            ->options([
                                'percentage' => 'Percentage',
                                'flat_fee' => 'Flat Fee',
                            ]),

                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->step(0.01)
                            ->label('Commission Rate'),

                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('USD'),

                        Forms\Components\TagsInput::make('tracking_params')
                            ->placeholder('Add tracking parameters...'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Link'),

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

                Tables\Columns\TextColumn::make('affiliate_provider')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'insurance',
                        'success' => 'accommodation',
                        'warning' => 'transport',
                        'info' => 'coworking',
                        'secondary' => 'food',
                    ]),

                Tables\Columns\BadgeColumn::make('commission_type')
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'flat_fee',
                    ]),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission Rate')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state ? $state.($record->commission_type === 'percentage' ? '%' : ' '.$record->currency) : '-'),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversion_count')
                    ->label('Conversions')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Total Commission')
                    ->money('USD')
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
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'insurance' => 'Insurance',
                        'accommodation' => 'Accommodation',
                        'transport' => 'Transport',
                        'coworking' => 'Coworking',
                        'food' => 'Food',
                        'entertainment' => 'Entertainment',
                        'shopping' => 'Shopping',
                        'utilities' => 'Utilities',
                    ]),

                Tables\Filters\SelectFilter::make('commission_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'flat_fee' => 'Flat Fee',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Links'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Links'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAffiliateLinks::route('/'),
            'create' => Pages\CreateAffiliateLink::route('/create'),
            'edit' => Pages\EditAffiliateLink::route('/{record}/edit'),
        ];
    }
}

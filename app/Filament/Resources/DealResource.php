<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealResource\Pages;
use App\Models\Deal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Deals';

    protected static ?string $modelLabel = 'Deal';

    protected static ?string $pluralModelLabel = 'Deals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Deal Information')
                    ->schema([
                        Forms\Components\Select::make('affiliate_link_id')
                            ->label('Affiliate Link')
                            ->relationship('affiliateLink', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('title')
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
                            ->unique(Deal::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('deal_url')
                            ->url()
                            ->label('Deal URL'),

                        Forms\Components\TextInput::make('provider')
                            ->maxLength(255)
                            ->placeholder('e.g., SafetyWing, Airbnb'),

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

                        Forms\Components\TextInput::make('original_price')
                            ->numeric()
                            ->step(0.01)
                            ->label('Original Price'),

                        Forms\Components\TextInput::make('discounted_price')
                            ->numeric()
                            ->step(0.01)
                            ->label('Discounted Price'),

                        Forms\Components\TextInput::make('discount_percentage')
                            ->numeric()
                            ->step(0.01)
                            ->label('Discount Percentage'),

                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('USD'),

                        Forms\Components\TextInput::make('promo_code')
                            ->maxLength(255)
                            ->label('Promo Code'),

                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Valid From')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Valid Until')
                            ->default(now()->addMonths(3)),

                        Forms\Components\TagsInput::make('terms_conditions')
                            ->placeholder('Add terms and conditions...'),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('deals')
                            ->visibility('public'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Deal'),

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
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('affiliateLink.name')
                    ->label('Affiliate Link')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'insurance',
                        'success' => 'accommodation',
                        'warning' => 'transport',
                        'info' => 'coworking',
                        'secondary' => 'food',
                    ]),

                Tables\Columns\TextColumn::make('original_price')
                    ->label('Original Price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discounted_price')
                    ->label('Discounted Price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-'),

                Tables\Columns\TextColumn::make('promo_code')
                    ->label('Promo Code')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversion_count')
                    ->label('Conversions')
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
                Tables\Filters\SelectFilter::make('affiliate_link')
                    ->relationship('affiliateLink', 'name')
                    ->searchable()
                    ->preload(),

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

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Deals'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Deals'),

                Tables\Filters\Filter::make('valid_now')
                    ->query(fn (Builder $query): Builder => $query->where('valid_from', '<=', now())->where('valid_until', '>=', now()))
                    ->label('Currently Valid'),
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
            'index' => Pages\ListDeals::route('/'),
            'create' => Pages\CreateDeal::route('/create'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }
}

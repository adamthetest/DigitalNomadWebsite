<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Filament\Resources\NewsletterSubscriberResource\RelationManagers;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter Subscribers';

    protected static ?string $modelLabel = 'Newsletter Subscriber';

    protected static ?string $pluralModelLabel = 'Newsletter Subscribers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscriber Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(NewsletterSubscriber::class, 'email', ignoreRecord: true),
                        
                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('country_code')
                            ->maxLength(2)
                            ->label('Country Code')
                            ->placeholder('e.g., US, UK, CA'),
                        
                        Forms\Components\TagsInput::make('interests')
                            ->placeholder('Add interests...'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'subscribed' => 'Subscribed',
                                'unsubscribed' => 'Unsubscribed',
                                'bounced' => 'Bounced',
                                'complained' => 'Complained',
                            ])
                            ->required()
                            ->default('subscribed'),
                        
                        Forms\Components\TextInput::make('source')
                            ->maxLength(255)
                            ->placeholder('e.g., homepage, blog, social'),
                        
                        Forms\Components\TagsInput::make('utm_data')
                            ->placeholder('Add UTM data...'),
                        
                        Forms\Components\DateTimePicker::make('last_email_sent')
                            ->label('Last Email Sent'),
                        
                        Forms\Components\DateTimePicker::make('subscribed_at')
                            ->label('Subscribed At')
                            ->default(now()),
                        
                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Unsubscribed At'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'subscribed',
                        'danger' => 'unsubscribed',
                        'warning' => 'bounced',
                        'secondary' => 'complained',
                    ]),
                
                Tables\Columns\TextColumn::make('source')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('last_email_sent')
                    ->label('Last Email')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'subscribed' => 'Subscribed',
                        'unsubscribed' => 'Unsubscribed',
                        'bounced' => 'Bounced',
                        'complained' => 'Complained',
                    ]),
                
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'homepage' => 'Homepage',
                        'blog' => 'Blog',
                        'social' => 'Social Media',
                        'referral' => 'Referral',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\Filter::make('subscribed_recently')
                    ->query(fn (Builder $query): Builder => $query->where('subscribed_at', '>=', now()->subDays(30)))
                    ->label('Subscribed in Last 30 Days'),
                
                Tables\Filters\Filter::make('never_emailed')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_email_sent'))
                    ->label('Never Emailed'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
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
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(160)
                            ->helperText('Short description (max 160 characters)'),
                        Forms\Components\Textarea::make('bio')
                            ->label('Bio')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\TextInput::make('timezone')
                            ->label('Timezone')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Professional Details')
                    ->schema([
                        Forms\Components\TextInput::make('job_title')
                            ->label('Job Title')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company')
                            ->label('Company')
                            ->maxLength(255),
                        Forms\Components\TagsInput::make('skills')
                            ->label('Skills')
                            ->placeholder('Add skills...')
                            ->helperText('Press Enter to add a skill'),
                        Forms\Components\Select::make('work_type')
                            ->label('Work Type')
                            ->options([
                                'freelancer' => 'Freelancer',
                                'employee' => 'Remote Employee',
                                'entrepreneur' => 'Entrepreneur',
                            ])
                            ->placeholder('Select work type'),
                        Forms\Components\TextInput::make('availability')
                            ->label('Availability')
                            ->maxLength(255)
                            ->helperText('e.g., "Available for projects", "Open to opportunities"'),
                    ])->columns(2),

                Forms\Components\Section::make('Nomad Lifestyle')
                    ->schema([
                        Forms\Components\TextInput::make('location_current')
                            ->label('Current Location')
                            ->maxLength(255)
                            ->helperText('City, Country format'),
                        Forms\Components\TextInput::make('location_next')
                            ->label('Next Destination')
                            ->maxLength(255)
                            ->helperText('Where are you planning to go next?'),
                        Forms\Components\Repeater::make('travel_timeline')
                            ->label('Travel Timeline')
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->label('City')
                                    ->required(),
                                Forms\Components\TextInput::make('country')
                                    ->label('Country')
                                    ->required(),
                                Forms\Components\DatePicker::make('arrived_at')
                                    ->label('Arrived At'),
                                Forms\Components\DatePicker::make('left_at')
                                    ->label('Left At'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['city'] ?? null),
                    ])->columns(1),

                Forms\Components\Section::make('Social Links')
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('github')
                            ->label('GitHub')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('twitter')
                            ->label('Twitter/X')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('behance')
                            ->label('Behance')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Privacy & Status')
                    ->schema([
                        Forms\Components\Select::make('visibility')
                            ->label('Profile Visibility')
                            ->options([
                                'public' => 'Public',
                                'members' => 'Members Only',
                                'hidden' => 'Hidden',
                            ])
                            ->default('public'),
                        Forms\Components\Toggle::make('location_precise')
                            ->label('Show Precise Location')
                            ->helperText('If disabled, only country will be shown'),
                        Forms\Components\Toggle::make('show_social_links')
                            ->label('Show Social Links')
                            ->default(true),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public Profile')
                            ->default(true),
                        Forms\Components\Toggle::make('id_verified')
                            ->label('ID Verified'),
                        Forms\Components\Toggle::make('premium_status')
                            ->label('Premium Member'),
                        Forms\Components\DateTimePicker::make('last_active')
                            ->label('Last Active')
                            ->displayFormat('M j, Y g:i A'),
                    ])->columns(2),

                Forms\Components\Section::make('System Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->displayFormat('M j, Y g:i A'),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Joined At')
                            ->displayFormat('M j, Y g:i A')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Last Updated')
                            ->displayFormat('M j, Y g:i A')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->minLength(8)
                            ->label('Password')
                            ->helperText('Leave blank to keep current password when editing'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('tagline')
                    ->label('Tagline')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->label('Job Title')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location_current')
                    ->label('Current Location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('id_verified')
                    ->label('ID Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('premium_status')
                    ->label('Premium')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibility')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'members' => 'warning',
                        'hidden' => 'danger',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_active')
                    ->label('Last Active')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('favorites_count')
                    ->label('Favorites')
                    ->counts('favorites')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Email Verified'),
                Tables\Filters\Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at'))
                    ->label('Email Unverified'),
                Tables\Filters\Filter::make('id_verified')
                    ->query(fn (Builder $query): Builder => $query->where('id_verified', true))
                    ->label('ID Verified'),
                Tables\Filters\Filter::make('premium')
                    ->query(fn (Builder $query): Builder => $query->where('premium_status', true))
                    ->label('Premium Members'),
                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        'public' => 'Public',
                        'members' => 'Members Only',
                        'hidden' => 'Hidden',
                    ]),
                Tables\Filters\SelectFilter::make('work_type')
                    ->options([
                        'freelancer' => 'Freelancer',
                        'employee' => 'Remote Employee',
                        'entrepreneur' => 'Entrepreneur',
                    ]),
                Tables\Filters\Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Joined Last 30 Days'),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('last_active', '>=', now()->subDays(7)))
                    ->label('Active Last 7 Days'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify_email')
                    ->label('Verify Email')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => is_null($record->email_verified_at))
                    ->action(function (User $record) {
                        $record->update(['email_verified_at' => now()]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('verify_id')
                    ->label('Verify ID')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! $record->id_verified)
                    ->action(function (User $record) {
                        $record->update(['id_verified' => true]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('toggle_premium')
                    ->label(fn (User $record): string => $record->premium_status ? 'Remove Premium' : 'Make Premium')
                    ->icon(fn (User $record): string => $record->premium_status ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn (User $record): string => $record->premium_status ? 'gray' : 'warning')
                    ->action(function (User $record) {
                        $record->update(['premium_status' => ! $record->premium_status]);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_emails')
                        ->label('Verify Selected Emails')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['email_verified_at' => now()]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('verify_ids')
                        ->label('Verify Selected IDs')
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['id_verified' => true]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('make_premium')
                        ->label('Make Selected Premium')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['premium_status' => true]);
                            });
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityLogResource\Pages;
use App\Filament\Resources\SecurityLogResource\RelationManagers;
use App\Models\SecurityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SecurityLogResource extends Resource
{
    protected static ?string $model = SecurityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Security Logs';

    protected static ?string $modelLabel = 'Security Log';

    protected static ?string $pluralModelLabel = 'Security Logs';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Security Log Details')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_agent')
                            ->label('User Agent')
                            ->disabled(),
                        Forms\Components\Select::make('event_type')
                            ->label('Event Type')
                            ->options([
                                'login_attempt' => 'Login Attempt',
                                'failed_login' => 'Failed Login',
                                'successful_login' => 'Successful Login',
                                'banned_access' => 'Banned Access',
                                'admin_access' => 'Admin Access',
                                'ip_banned' => 'IP Banned',
                                'ip_unbanned' => 'IP Unbanned',
                            ])
                            ->disabled(),
                        Forms\Components\Select::make('severity')
                            ->label('Severity')
                            ->options([
                                'info' => 'Info',
                                'warning' => 'Warning',
                                'error' => 'Error',
                                'critical' => 'Critical',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->disabled(),
                        Forms\Components\TextInput::make('method')
                            ->label('Method')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('IP address copied'),
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'failed_login' => 'danger',
                        'banned_access' => 'danger',
                        'ip_banned' => 'warning',
                        'successful_login' => 'success',
                        'admin_access' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'error' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'POST' => 'warning',
                        'PUT' => 'warning',
                        'DELETE' => 'danger',
                        'GET' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'login_attempt' => 'Login Attempt',
                        'failed_login' => 'Failed Login',
                        'successful_login' => 'Successful Login',
                        'banned_access' => 'Banned Access',
                        'admin_access' => 'Admin Access',
                        'ip_banned' => 'IP Banned',
                        'ip_unbanned' => 'IP Unbanned',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Severity')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'error' => 'Error',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subHours(24)))
                    ->label('Last 24 Hours'),
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Today'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('ban_ip')
                    ->label('Ban IP')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->action(function (SecurityLog $record) {
                        \App\Models\BannedIp::banIp(
                            $record->ip_address,
                            'Banned from security logs',
                            auth()->id()
                        );
                        \App\Models\SecurityLog::logIpBan($record->ip_address, 'Banned from security logs', auth()->user());
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Ban IP Address')
                    ->modalDescription('Are you sure you want to ban this IP address?'),
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
            'index' => Pages\ListSecurityLogs::route('/'),
            'create' => Pages\CreateSecurityLog::route('/create'),
            'edit' => Pages\EditSecurityLog::route('/{record}/edit'),
        ];
    }
}

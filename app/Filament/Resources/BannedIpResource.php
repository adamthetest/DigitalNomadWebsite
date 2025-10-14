<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannedIpResource\Pages;
use App\Models\BannedIp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BannedIpResource extends Resource
{
    protected static ?string $model = BannedIp::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Banned IPs';

    protected static ?string $modelLabel = 'Banned IP';

    protected static ?string $pluralModelLabel = 'Banned IPs';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('IP Ban Information')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->required()
                            ->maxLength(45)
                            ->label('IP Address')
                            ->placeholder('192.168.1.1'),
                        Forms\Components\Textarea::make('reason')
                            ->label('Ban Reason')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Enter reason for banning this IP address'),
                        Forms\Components\Select::make('banned_by')
                            ->label('Banned By')
                            ->relationship('bannedBy', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->displayFormat('M j, Y g:i A')
                            ->placeholder('Leave empty for permanent ban'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('IP address copied'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('bannedBy.name')
                    ->label('Banned By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('banned_at')
                    ->label('Banned At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('Permanent'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Bans'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<=', now()))
                    ->label('Expired Bans'),
                Tables\Filters\Filter::make('permanent')
                    ->query(fn (Builder $query): Builder => $query->whereNull('expires_at'))
                    ->label('Permanent Bans'),
            ])
            ->actions([
                Tables\Actions\Action::make('unban')
                    ->label('Unban')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BannedIp $record): bool => $record->is_active)
                    ->action(function (BannedIp $record) {
                        $record->update(['is_active' => false]);
                        \App\Models\SecurityLog::logIpUnban($record->ip_address, auth()->user());
                    })
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('unban_selected')
                        ->label('Unban Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                                \App\Models\SecurityLog::logIpUnban($record->ip_address, auth()->user());
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('banned_at', 'desc');
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
            'index' => Pages\ListBannedIps::route('/'),
            'create' => Pages\CreateBannedIp::route('/create'),
            'edit' => Pages\EditBannedIp::route('/{record}/edit'),
        ];
    }
}

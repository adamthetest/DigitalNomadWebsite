<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Jobs';

    protected static ?string $modelLabel = 'Job';

    protected static ?string $pluralModelLabel = 'Jobs';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4),
                        Forms\Components\Textarea::make('requirements')
                            ->rows(3),
                        Forms\Components\Textarea::make('benefits')
                            ->rows(3),
                    ])->columns(1),

                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'full-time' => 'Full Time',
                                'part-time' => 'Part Time',
                                'contract' => 'Contract',
                                'freelance' => 'Freelance',
                                'internship' => 'Internship',
                            ])
                            ->required()
                            ->default('full-time'),
                        Forms\Components\Select::make('remote_type')
                            ->options([
                                'fully-remote' => 'Fully Remote',
                                'hybrid' => 'Hybrid',
                                'timezone-limited' => 'Timezone Limited',
                                'onsite' => 'On-site',
                            ])
                            ->required()
                            ->default('fully-remote'),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('timezone')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Compensation')
                    ->schema([
                        Forms\Components\TextInput::make('salary_min')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('salary_max')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('salary_currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'CAD' => 'CAD',
                            ])
                            ->default('USD'),
                        Forms\Components\Select::make('salary_period')
                            ->options([
                                'yearly' => 'Yearly',
                                'monthly' => 'Monthly',
                                'hourly' => 'Hourly',
                            ])
                            ->default('yearly'),
                    ])->columns(2),

                Forms\Components\Section::make('Skills & Requirements')
                    ->schema([
                        Forms\Components\TagsInput::make('tags')
                            ->placeholder('Add skill tags...'),
                        Forms\Components\TagsInput::make('experience_level')
                            ->placeholder('Add experience levels...'),
                    ])->columns(1),

                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\TextInput::make('apply_url')
                            ->url()
                            ->required(),
                        Forms\Components\TextInput::make('apply_email')
                            ->email(),
                        Forms\Components\TextInput::make('source_url')
                            ->url(),
                    ])->columns(1),

                Forms\Components\Section::make('Status & Settings')
                    ->schema([
                        Forms\Components\Toggle::make('featured')
                            ->label('Featured Job'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('visa_support')
                            ->label('Visa Support'),
                        Forms\Components\Select::make('source')
                            ->options([
                                'manual' => 'Manual',
                                'scraped' => 'Scraped',
                                'api' => 'API',
                            ])
                            ->default('manual'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->default(now()->addDays(30)),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full-time' => 'success',
                        'part-time' => 'warning',
                        'contract' => 'info',
                        'freelance' => 'gray',
                        'internship' => 'purple',
                    }),
                Tables\Columns\TextColumn::make('remote_type')
                    ->label('Remote')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fully-remote' => 'success',
                        'hybrid' => 'warning',
                        'timezone-limited' => 'info',
                        'onsite' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('formatted_salary')
                    ->label('Salary')
                    ->sortable(['salary_min', 'salary_max'])
                    ->toggleable(),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('visa_support')
                    ->label('Visa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'success',
                        'scraped' => 'info',
                        'api' => 'warning',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Applications')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'full-time' => 'Full Time',
                        'part-time' => 'Part Time',
                        'contract' => 'Contract',
                        'freelance' => 'Freelance',
                        'internship' => 'Internship',
                    ]),
                Tables\Filters\SelectFilter::make('remote_type')
                    ->options([
                        'fully-remote' => 'Fully Remote',
                        'hybrid' => 'Hybrid',
                        'timezone-limited' => 'Timezone Limited',
                        'onsite' => 'On-site',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'manual' => 'Manual',
                        'scraped' => 'Scraped',
                        'api' => 'API',
                    ]),
                Tables\Filters\Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('featured', true))
                    ->label('Featured Jobs'),
                Tables\Filters\Filter::make('visa_support')
                    ->query(fn (Builder $query): Builder => $query->where('visa_support', true))
                    ->label('Visa Support'),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Jobs'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('feature')
                    ->label('Feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Job $record): bool => !$record->featured)
                    ->action(function (Job $record) {
                        $record->update(['featured' => true]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unfeature')
                    ->label('Unfeature')
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (Job $record): bool => $record->featured)
                    ->action(function (Job $record) {
                        $record->update(['featured' => false]);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['featured' => true]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
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
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}

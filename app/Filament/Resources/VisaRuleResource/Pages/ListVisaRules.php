<?php

namespace App\Filament\Resources\VisaRuleResource\Pages;

use App\Filament\Resources\VisaRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisaRules extends ListRecords
{
    protected static string $resource = VisaRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

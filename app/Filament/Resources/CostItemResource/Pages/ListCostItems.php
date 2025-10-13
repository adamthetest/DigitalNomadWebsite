<?php

namespace App\Filament\Resources\CostItemResource\Pages;

use App\Filament\Resources\CostItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostItems extends ListRecords
{
    protected static string $resource = CostItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

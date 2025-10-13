<?php

namespace App\Filament\Resources\CostItemResource\Pages;

use App\Filament\Resources\CostItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCostItem extends EditRecord
{
    protected static string $resource = CostItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

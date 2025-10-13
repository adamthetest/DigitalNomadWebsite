<?php

namespace App\Filament\Resources\BannedIpResource\Pages;

use App\Filament\Resources\BannedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannedIps extends ListRecords
{
    protected static string $resource = BannedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

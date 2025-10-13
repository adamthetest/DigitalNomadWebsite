<?php

namespace App\Filament\Resources\BannedIpResource\Pages;

use App\Filament\Resources\BannedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannedIp extends EditRecord
{
    protected static string $resource = BannedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

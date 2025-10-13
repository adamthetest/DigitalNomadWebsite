<?php

namespace App\Filament\Resources\CoworkingSpaceResource\Pages;

use App\Filament\Resources\CoworkingSpaceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoworkingSpace extends EditRecord
{
    protected static string $resource = CoworkingSpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\BannedIpResource\Pages;

use App\Filament\Resources\BannedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannedIp extends CreateRecord
{
    protected static string $resource = BannedIpResource::class;
}

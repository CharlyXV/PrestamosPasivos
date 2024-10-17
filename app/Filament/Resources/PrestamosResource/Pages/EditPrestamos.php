<?php

namespace App\Filament\Resources\PrestamosResource\Pages;

use App\Filament\Resources\PrestamosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrestamos extends EditRecord
{
    protected static string $resource = PrestamosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

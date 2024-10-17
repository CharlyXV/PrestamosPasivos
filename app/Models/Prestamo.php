<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestamo extends Model
{
    use HasFactory;
    public function banco(): BeLongsTo
    {
        return $this->beLongsTo(Banco::class);
        
    }

    public function empresas(): BeLongsTo
    {
        return $this->beLongsTo(Empresa::class);
        
    }
    
    public function lineas(): BeLongsTo
    {
        return $this->beLongsTo(Linea::class);
        
    }
    
    public function productos(): BeLongsTo
    {
        return $this->beLongsTo(Producto::class);
        
    }
    
    public function tipotasas(): BeLongsTo
    {
        return $this->beLongsTo(Tipotasa::class);
        
    }
    

    public function planpagos(): HasMany
    {
        return $this->hasMany(Planpago::class);
        
    }


}

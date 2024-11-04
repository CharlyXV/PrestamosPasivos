<?php

namespace App\Models;

use App\Casts\LoanController;
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

    public function empresa(): BeLongsTo
    {
        return $this->beLongsTo(Empresa::class);
        
    }
    
    public function linea(): BeLongsTo
    {
        return $this->beLongsTo(Linea::class);
        
    }
    
    public function producto(): BeLongsTo
    {
        return $this->beLongsTo(Producto::class);
        
    }
    
    public function tipotasa(): BeLongsTo
    {
        return $this->beLongsTo(Tipotasa::class);
        
    }
    

    public function planpago(): HasMany
    {
        return $this->hasMany(Planpago::class);
        
    }


}

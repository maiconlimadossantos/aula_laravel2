<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'preco_unitario',
        'tipo_medida',
        'quantidade_medida'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'imagem_url',
        'preco',
        'quantidade_estoque',
        'fornecedor',
        'data_validade',
        'tipo_medida',
    ];
}

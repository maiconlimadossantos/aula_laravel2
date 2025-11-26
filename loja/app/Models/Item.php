<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa (mass assignable).
     * Correspondem aos campos do formulário/tabela.
     */
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

    /**
     * Converte o campo de preço para float ao recuperar do DB.
     * (Opcional, se o campo for armazenado como string/decimal)
     */
    protected $casts = [
        'preco' => 'float',
        'quantidade_estoque' => 'integer',
        'data_validade' => 'date',
    ];
    
    // Método para formatar o preço para exibição (usado na View)
    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }
}
?>
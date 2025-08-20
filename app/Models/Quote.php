<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importe a classe
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importe a classe

class Quote extends Model
{
    /**
     * Atributos que podem ser preenchidos em massa.
     */
    
    protected $fillable = [
        'unique_hash',
        'client_name',
        'client_contact',
        'user_id',
        'status',
        'total_amount',
        'payment_terms',
        'delivery_info',
    ];

    /**
     * RELACIONAMENTO: Um orçamento pertence a um usuário (vendedor).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELACIONAMENTO: Um orçamento pode ter muitos produtos.
     */
    public function products(): BelongsToMany
    {
        // A relação é 'belongsToMany' (muitos para muitos) com a tabela Product.
        return $this->belongsToMany(Product::class, 'quote_product')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}

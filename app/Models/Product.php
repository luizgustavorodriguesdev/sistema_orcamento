<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'promotional_price',
        'image_url',
        'category_id'
    ];

    /**
     * RELAÇÃO: Um produto pertence a uma categoria.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * RELACIONAMENTO: Um produto pode pertencer a muitos orçamentos.
     */
    public function quotes(): BelongsToMany
    {
        // A relação é 'belongsToMany' (muitos para muitos) com a tabela Quote.
        // O método withPivot nos permite acessar as colunas extras da tabela pivot.
        return $this->belongsToMany(Quote::class, 'quote_product')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}

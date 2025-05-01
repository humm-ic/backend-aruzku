<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type', // 'expense' or 'income'
        'icon',
    ];

    /**
     * Get the expenses for the category.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the incomes for the category.
     */
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}

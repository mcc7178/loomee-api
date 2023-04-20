<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 */
class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Product';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleShop extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'sale_shops';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

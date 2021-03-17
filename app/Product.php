<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'products';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

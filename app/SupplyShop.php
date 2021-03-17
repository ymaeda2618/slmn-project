<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplyShop extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'supply_shops';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

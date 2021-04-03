<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderSupplyUnitPriceDetail extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'order_supply_unit_price_details';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
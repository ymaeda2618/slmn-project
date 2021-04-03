<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderSaleUnitPriceDetail extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'order_sale_unit_price_details';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
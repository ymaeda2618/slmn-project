<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryManage extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'inventory_manages';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

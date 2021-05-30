<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'expense_items';

}

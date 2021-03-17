<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'standards';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OriginArea extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'origin_areas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}

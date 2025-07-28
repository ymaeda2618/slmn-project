<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompanySetting extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'company_settings';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    /**
     * 企業情報を取得
     *
     * @param int companyId 企業ID
     * @return \Illuminate\Support\Collection
     */
    public static function getCompanyData()
    {
        // 企業IDを取得
        $companyId = env('COMPANY_ID', '0001');

        return DB::table((new self)->table . ' AS CompanySetting')
                ->where('company_id', $companyId)
                ->get();
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}

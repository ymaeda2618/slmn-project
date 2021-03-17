<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SaleSlip extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'sale_slips';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    /**
     * 売上伝票の新規登録
     * @param array $SaleSlipData
     *
     * @return array SaleSlipモデル
     */
    public function insertSaleSlip(array $SaleSlipData)
    {
        // 値がNULLのところを初期化
        if (empty($SaleSlipData['sale_shop_id'])) $SaleSlipData['sale_shop_id'] = 0;
        if (empty($SaleSlipData['delivery_id'])) $SaleSlipData['delivery_id'] = 0;
        if (empty($SaleSlipData['delivery_price'])) $SaleSlipData['delivery_price'] = 0;
        if (empty($SaleSlipData['adjust_price'])) $SaleSlipData['adjust_price'] = 0;

        $this->date               = $SaleSlipData['sale_date'];            // 日付
        $this->delivery_date      = $SaleSlipData['delivery_date'];        // 納品日
        $this->sale_company_id    = $SaleSlipData['sale_company_id'];      // 売上先ID
        $this->sale_shop_id       = $SaleSlipData['sale_shop_id'];         // 売上先店舗ID
        $this->delivery_id        = $SaleSlipData['delivery_id'];          // 配送ID
        $this->notax_sub_total_8  = $SaleSlipData['notax_sub_total_8'];    // 8%課税対象額
        $this->notax_sub_total_10 = $SaleSlipData['notax_sub_total_10'];   // 10%課税対象額
        $this->notax_sub_total    = $SaleSlipData['notax_sub_total'];      // 税抜合計額
        $this->tax_total_8        = $SaleSlipData['tax_total_8'];          // 8%課税対象額
        $this->tax_total_10       = $SaleSlipData['tax_total_10'];         // 10%課税対象額
        $this->tax_total          = $SaleSlipData['tax_total'];            // 税抜合計額
        $this->sub_total_8        = $SaleSlipData['sub_total_8'];          // 8%合計額
        $this->sub_total_10       = $SaleSlipData['sub_total_10'];         // 10%合計額
        $this->delivery_price     = $SaleSlipData['delivery_price'];       // 合計額
        $this->sub_total          = $SaleSlipData['sub_total'];            // 配送額
        $this->adjust_price       = $SaleSlipData['adjust_price'];         // 調整額
        $this->total              = $SaleSlipData['total'];                // 合計額
        $this->remarks            = $SaleSlipData['remarks'];              // 備考
        $this->sale_submit_type   = $SaleSlipData['sale_submit_type'];     // 登録タイプ
        $this->sort               = 100;                                   // ソート
        $this->created_user_id    = $SaleSlipData['user_id'];              // 作成者ユーザーID
        $this->created            = Carbon::now();                         // 作成時間
        $this->modified_user_id   = $SaleSlipData['user_id'];              // 更新者ユーザーID
        $this->modified           = Carbon::now();                         // 更新時間
        $this->save();

        // モデルを返す
        return $this;
    }
}

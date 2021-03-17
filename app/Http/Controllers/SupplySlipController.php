<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use App\SupplySlip;
use App\SupplySlipDetail;
use Carbon\Carbon;

class SupplySlipController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 仕入一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if(count($path_array) > 1){
            $search_action = '../SupplySlipIndex';
        } else {
            $search_action = './SupplySlipIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type     = $request->session()->get('condition_date_type');
            $condition_date_from     = $request->session()->get('condition_date_from');
            $condition_date_to       = $request->session()->get('condition_date_to');

            // 空値の場合は初期値を設定
            if(empty($condition_date_type)) $condition_date_type = 1;
            if(empty($condition_date_from)) $condition_date_from = date('Y-m-d');
            if(empty($condition_date_to)) $condition_date_to     = date('Y-m-d');

            $condition_company_code  = $request->session()->get('condition_company_code');
            $condition_company_id    = $request->session()->get('condition_company_id');
            $condition_company_text  = $request->session()->get('condition_company_text');
            $condition_shop_code     = $request->session()->get('condition_shop_code');
            $condition_shop_id       = $request->session()->get('condition_shop_id');
            $condition_shop_text     = $request->session()->get('condition_shop_text');
            $condition_product_code  = $request->session()->get('condition_product_code');
            $condition_product_id    = $request->session()->get('condition_product_id');
            $condition_product_text  = $request->session()->get('condition_product_text');
            $condition_submit_type   = $request->session()->get('condition_submit_type');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type     = $request->data['SupplySlip']['date_type'];
                $condition_company_code  = $request->data['SupplySlip']['supply_company_code'];
                $condition_company_id    = $request->data['SupplySlip']['supply_company_id'];
                $condition_company_text  = $request->data['SupplySlip']['supply_company_text'];
                $condition_shop_code     = $request->data['SupplySlip']['supply_shop_code'];
                $condition_shop_id       = $request->data['SupplySlip']['supply_shop_id'];
                $condition_shop_text     = $request->data['SupplySlip']['supply_shop_text'];
                $condition_product_code  = $request->data['SupplySlipDetail']['product_code'];
                $condition_product_id    = $request->data['SupplySlipDetail']['product_id'];
                $condition_product_text  = $request->data['SupplySlipDetail']['product_text'];
                $condition_submit_type   = isset($request->data['SupplySlip']['supply_submit_type']) ? $request->data['SupplySlip']['supply_submit_type'] : 0;

                // 日付の設定
                $condition_date_from     = $request->data['SupplySlip']['supply_date_from'];
                $condition_date_to       = $request->data['SupplySlip']['supply_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('condition_date_type', $condition_date_type);
                $request->session()->put('condition_date_from', $condition_date_from);
                $request->session()->put('condition_date_to', $condition_date_to);
                $request->session()->put('condition_company_code', $condition_company_code);
                $request->session()->put('condition_company_id', $condition_company_id);
                $request->session()->put('condition_company_text', $condition_company_text);
                $request->session()->put('condition_shop_code', $condition_shop_code);
                $request->session()->put('condition_shop_id', $condition_shop_id);
                $request->session()->put('condition_shop_text', $condition_shop_text);
                $request->session()->put('condition_product_code', $condition_product_code);
                $request->session()->put('condition_product_id', $condition_product_id);
                $request->session()->put('condition_product_text', $condition_product_text);
                $request->session()->put('condition_submit_type', $condition_submit_type);

            } else { // リセットボタンが押された時の処理

                $condition_date_type     = 1;
                $condition_date_from     = date('Y-m-d');
                $condition_date_to       = date('Y-m-d');
                $condition_company_code  = null;
                $condition_company_id    = null;
                $condition_company_text  = null;
                $condition_shop_code     = null;
                $condition_shop_id       = null;
                $condition_shop_text     = null;
                $condition_product_code  = null;
                $condition_product_id    = null;
                $condition_product_text  = null;
                $condition_submit_type   = 0;
                $request->session()->forget('condition_date_type');
                $request->session()->forget('condition_date_from');
                $request->session()->forget('condition_date_to');
                $request->session()->forget('condition_company_code');
                $request->session()->forget('condition_company_id');
                $request->session()->forget('condition_company_text');
                $request->session()->forget('condition_shop_code');
                $request->session()->forget('condition_shop_id');
                $request->session()->forget('condition_shop_text');
                $request->session()->forget('condition_product_code');
                $request->session()->forget('condition_product_id');
                $request->session()->forget('condition_product_text');
                $request->session()->forget('condition_submit_type');
            }
        }

        try {

            // supply_slip_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {

                $product_sub_query = DB::table('supply_slip_details as SubTable')
                ->select('SubTable.supply_slip_id AS supply_slip_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->groupBy('SubTable.supply_slip_id');
            }

            // 仕入一覧を取得
            $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlip.id                  AS supply_slip_id',
                'SupplySlip.delivery_price      AS delivery_price',
                'SupplySlip.adjust_price        AS adjust_price',
                'SupplySlip.notax_sub_total     AS notax_sub_total',
                'SupplySlip.supply_submit_type  AS supply_submit_type',
                'SupplyCompany.code             AS supply_company_code',
                'SupplyCompany.name             AS supply_company_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y/%m/%d")                AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i")      AS supply_slip_modified')
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $condition_shop_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SupplySlipDetail'), 'SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SupplySlip.supply_submit_type', '=', $condition_submit_type);
            })
            ->where('SupplySlip.active', '=', '1')
            ->orderBy('SupplySlip.date', 'desc')->paginate(5);

            // =======================================================

            // 伝票詳細を取得
            $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlip.id                  AS supply_slip_id',
                'SupplySlip.total               AS supply_slip_total',
                'SupplySlip.supply_submit_type  AS supply_submit_type',
                'SupplyCompany.code             AS supply_company_code',
                'SupplyCompany.name             AS supply_company_name',
                'Product.code                   AS product_code',
                'Product.name                   AS product_name',
                'Product.tax_id                 AS product_tax_id',
                'Standard.name                  AS standard_name',
                'SupplySlipDetail.id            AS supply_slip_detail_id',
                'SupplySlipDetail.unit_price    AS supply_slip_detail_unit_price',
                'SupplySlipDetail.unit_num      AS supply_slip_detail_unit_num',
                'Unit.name                      AS unit_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y/%m/%d")                AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i")      AS supply_slip_modified')

            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei)            AS staff_name')

            ->join('supply_slips as SupplySlip', function ($join) {
                $join->on('SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id')
                ->where('SupplySlip.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->leftJoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                ->where('Standard.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id')
                ->where('Staff.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SupplySlipDetail.inventory_unit_id')
                ->where('InventoryUnit.active', '=', true);
            })
            ->leftJoin('supply_companies as SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                ->where('SupplyCompany.active', '=', true);
            })
            /*->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $condition_shop_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($condition_product_id) {
                return $query->where('SupplySlipDetail.product_id', '=', $condition_product_id);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SupplySlip.supply_submit_type', '=', $condition_submit_type);
            })*/
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $supply_slip_detail_arr = array();


            // 伝票詳細で取得したDBをループ
            foreach($SupplySlipDetailList as $SupplySlipDetails){

                if(!isset($supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id])){
                    $supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id] = 0;
                }

                $supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id] += 1;

                $supply_slip_detail_arr[$SupplySlipDetails->supply_slip_id][] = [

                    'product_code'                  => $SupplySlipDetails->product_code,
                    'product_name'                  => $SupplySlipDetails->product_name,
                    'product_tax_id'                => $SupplySlipDetails->product_tax_id,
                    'standard_name'                 => $SupplySlipDetails->standard_name,
                    'supply_slip_detail_id'         => $SupplySlipDetails->supply_slip_detail_id,
                    'supply_slip_detail_unit_price' => $SupplySlipDetails->supply_slip_detail_unit_price,
                    'supply_slip_detail_unit_num'   => $SupplySlipDetails->supply_slip_detail_unit_num,
                    'unit_name'                     => $SupplySlipDetails->unit_name,
                    'staff_name'                    => $SupplySlipDetails->staff_name,
                ];
            }

            // 対象日付のチェック
            $check_str_slip_date = "";
            $check_str_deliver_date = "";
            if($condition_date_type == 1) $check_str_slip_date = "checked";
            else  $check_str_deliver_date = "checked";

        } catch (\Exception $e) {

            dd($e);

            return view('SupplySlip.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SupplySlip.index')->with([
            "search_action"                => $search_action,
            "check_str_slip_date"          => $check_str_slip_date,
            "check_str_deliver_date"       => $check_str_deliver_date,
            "condition_date_from"          => $condition_date_from,
            "condition_date_to"            => $condition_date_to,
            "condition_company_code"       => $condition_company_code,
            "condition_company_id"         => $condition_company_id,
            "condition_company_text"       => $condition_company_text,
            "condition_shop_code"          => $condition_shop_code,
            "condition_shop_id"            => $condition_shop_id,
            "condition_shop_text"          => $condition_shop_text,
            "condition_product_code"       => $condition_product_code,
            "condition_product_id"         => $condition_product_id,
            "condition_product_text"       => $condition_product_text,
            "condition_submit_type"        => $condition_submit_type,
            "supplySlipList"               => $supplySlipList,
            "SupplySlipDetailList"         => $SupplySlipDetailList,
            "supply_slip_detail_arr"       => $supply_slip_detail_arr,
            "supply_slip_detail_count_arr" => $supply_slip_detail_count_arr
        ]);
    }

    /**
     * 仕入編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($supply_slip_id)
    {
        // activeの変数を格納
        $this_active = 1;


        // 仕入伝票取得
        $SupplySlipList = DB::table('supply_slips AS SupplySlip')
        ->select(
            'SupplySlip.id                  AS supply_slip_id',
            'SupplySlip.notax_sub_total_8   AS notax_sub_total_8',
            'SupplySlip.notax_sub_total_10  AS notax_sub_total_10',
            'SupplySlip.notax_sub_total     AS notax_sub_total',
            'SupplySlip.tax_total_8         AS tax_total_8',
            'SupplySlip.tax_total_10        AS tax_total_10',
            'SupplySlip.tax_total           AS tax_total',
            'SupplySlip.sub_total_8         AS sub_total_8',
            'SupplySlip.sub_total_10        AS sub_total_10',
            'SupplySlip.sub_total           AS sub_total',
            'SupplySlip.delivery_price      AS delivery_price',
            'SupplySlip.adjust_price        AS adjust_price',
            'SupplySlip.total               AS total',
            'SupplySlip.remarks             AS remarks',
            'SupplySlip.supply_submit_type  AS supply_submit_type',
            'SupplyCompany.code             AS supply_company_code',
            'SupplyCompany.id               AS supply_company_id',
            'SupplyCompany.name             AS supply_company_name',
            'SupplyShop.code                AS supply_shop_code',
            'SupplyShop.id                  AS supply_shop_id',
            'SupplyShop.name                AS supply_shop_name',
            'Delivery.code                  AS delivery_code',
            'Delivery.id                    AS delivery_id',
            'Delivery.name                  AS delivery_name'
        )
        ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y-%m-%d") AS supply_slip_supply_date')
        ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y-%m-%d") AS supply_slip_delivery_date')
        ->join('supply_companies as SupplyCompany', function ($join) {
            $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                 ->where('SupplyCompany.active', '=', true);
        })
        ->leftJoin('supply_shops as SupplyShop', function ($join) {
            $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id')
                 ->where('SupplyShop.active', '=', true);
        })
        ->leftJoin('deliverys as Delivery', function ($join) {
            $join->on('Delivery.id', '=', 'SupplySlip.delivery_id')
                 ->where('Delivery.active', '=', true);
        })
        ->where([
            ['SupplySlip.id', '=', $supply_slip_id],
            ['SupplySlip.active', '=', $this_active],
        ])
        ->first();

        // 仕入伝票詳細取得
        $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
        ->select(
            'SupplySlipDetail.id                 AS supply_slip_detail_id',
            'SupplySlipDetail.unit_price         AS unit_price',
            'SupplySlipDetail.unit_num           AS unit_num',
            'SupplySlipDetail.notax_price        AS notax_price',
            'SupplySlipDetail.seri_no            AS seri_no',
            'SupplySlipDetail.inventory_unit_num AS inventory_unit_num',
            'SupplySlipDetail.memo               AS memo',
            'SupplySlipDetail.sort               AS sort',
            'Product.code                        AS product_code',
            'Product.id                          AS product_id',
            'Product.name                        AS product_name',
            'Tax.id                              AS tax_id',
            'Tax.name                            AS tax_name',
            'Standard.code                       AS standard_code',
            'Standard.id                         AS standard_id',
            'Standard.name                       AS standard_name',
            'Quality.code                        AS quality_code',
            'Quality.id                          AS quality_id',
            'Quality.name                        AS quality_name',
            'Unit.id                             AS unit_id',
            'Unit.name                           AS unit_name',
            'OriginArea.id                       AS origin_area_id',
            'OriginArea.name                     AS origin_area_name',
            'Staff.code                          AS staff_code',
            'Staff.id                            AS staff_id',
            'InventoryUnit.id                    AS inventory_unit_id',
            'InventoryUnit.name                  AS inventory_unit_name'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'SupplySlipDetail.product_id')
                 ->where('Product.active', '=', true);
        })
        ->join('taxes as Tax', function ($join) {
            $join->on('Tax.id', '=', 'Product.tax_id')
                 ->where('Tax.active', '=', true);
        })
        ->leftJoin('standards as Standard', function ($join) {
            $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                 ->where('Standard.active', '=', true);
        })
        ->leftJoin('qualities as Quality', function ($join) {
            $join->on('Quality.id', '=', 'SupplySlipDetail.quality_id')
                 ->where('Quality.active', '=', true);
        })
        ->join('units as Unit', function ($join) {
            $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id')
                 ->where('Unit.active', '=', true);
        })
        ->leftJoin('origin_areas as OriginArea', function ($join) {
            $join->on('OriginArea.id', '=', 'SupplySlipDetail.origin_area_id')
                 ->where('OriginArea.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->leftJoin('units as InventoryUnit', function ($join) {
            $join->on('InventoryUnit.id', '=', 'SupplySlipDetail.inventory_unit_id')
                 ->where('InventoryUnit.active', '=', true);
        })
        ->where([
            ['SupplySlipDetail.supply_slip_id', '=', $supply_slip_id],
            ['SupplySlipDetail.active', '=', $this_active],
        ])
        ->get();

        return view('SupplySlip.edit')->with([
            "SupplySlipList"        => $SupplySlipList,
            "SupplySlipDetailList"  => $SupplySlipDetailList
        ]);
    }

    /**
     * 編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request)
    {

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SupplySlipData = $request->data['SupplySlip'];
            $SupplySlipDetailData = $request->data['SupplySlipDetail'];

            // 値がNULLのところを初期化
            if(empty($SupplySlipData['supply_shop_id'])) $SupplySlipData['supply_shop_id'] = 0;
            if(empty($SupplySlipData['delivery_id'])) $SupplySlipData['delivery_id'] = 0;
            if(empty($SupplySlipData['delivery_price'])) $SupplySlipData['delivery_price'] = 0;
            if(empty($SupplySlipData['adjust_price'])) $SupplySlipData['adjust_price'] = 0;

            // supply_slipsを登録する
            $SupplySlip = \App\SupplySlip::find($SupplySlipData['id']);
            $SupplySlip->date               = $SupplySlipData['supply_date'];          // 日付
            $SupplySlip->delivery_date      = $SupplySlipData['delivery_date'];        // 納品日
            $SupplySlip->supply_company_id  = $SupplySlipData['supply_company_id'];    // 仕入先ID
            $SupplySlip->supply_shop_id     = $SupplySlipData['supply_shop_id'];       // 仕入先店舗ID
            $SupplySlip->delivery_id        = $SupplySlipData['delivery_id'];          // 配送ID
            $SupplySlip->notax_sub_total_8  = $SupplySlipData['notax_sub_total_8'];    // 8%課税対象額
            $SupplySlip->notax_sub_total_10 = $SupplySlipData['notax_sub_total_10'];   // 10%課税対象額
            $SupplySlip->notax_sub_total    = $SupplySlipData['notax_sub_total'];      // 税抜合計額
            $SupplySlip->tax_total_8        = $SupplySlipData['tax_total_8'];          // 8%課税対象額
            $SupplySlip->tax_total_10       = $SupplySlipData['tax_total_10'];         // 10%課税対象額
            $SupplySlip->tax_total          = $SupplySlipData['tax_total'];            // 税抜合計額
            $SupplySlip->sub_total_8        = $SupplySlipData['sub_total_8'];          // 8%合計額
            $SupplySlip->sub_total_10       = $SupplySlipData['sub_total_10'];         // 10%合計額
            $SupplySlip->delivery_price     = $SupplySlipData['delivery_price'];       // 合計額
            $SupplySlip->sub_total          = $SupplySlipData['sub_total'];            // 配送額
            $SupplySlip->adjust_price       = $SupplySlipData['adjust_price'];         // 調整額
            $SupplySlip->total              = $SupplySlipData['total'];                // 合計額
            $SupplySlip->remarks            = $SupplySlipData['remarks'];              // 備考
            $SupplySlip->supply_submit_type = $SupplySlipData['supply_submit_type'];   // 登録タイプ
            $SupplySlip->modified_user_id   = $user_info_id;                           // 更新者ユーザーID
            $SupplySlip->modified           = Carbon::now();                           // 更新時間

            $SupplySlip->save();

            // 作成したIDを取得する
            $supply_slip_new_id = $SupplySlip->id;

            // 伝票詳細を削除
            \App\SupplySlipDetail::where('supply_slip_id', $SupplySlipData['id'])->delete();

            $supply_slip_detail = array();
            $sort = 0;

            foreach($SupplySlipDetailData as $SupplySlipDetail){

                // 値がNULLのところを初期化
                if (empty($SupplySlipData['standard_id'])) $SupplySlipData['standard_id'] = 0;
                if (empty($SupplySlipData['quality_id'])) $SupplySlipData['quality_id'] = 0;
                if (empty($SupplySlipData['origin_area_id'])) $SupplySlipData['origin_area_id'] = 0;

                $supply_slip_detail[] = [
                    'supply_slip_id'     => $supply_slip_new_id,
                    'product_id'         => $SupplySlipDetail['product_id'],
                    'standard_id'        => $SupplySlipDetail['standard_id'],
                    'quality_id'         => $SupplySlipDetail['quality_id'],
                    'unit_price'         => $SupplySlipDetail['unit_price'],
                    'unit_num'           => $SupplySlipDetail['unit_num'],
                    'notax_price'        => $SupplySlipDetail['notax_price'],
                    'unit_id'            => $SupplySlipDetail['unit_id'],
                    'origin_area_id'     => $SupplySlipDetail['origin_area_id'],
                    'staff_id'           => $SupplySlipDetail['staff_id'],
                    'seri_no'            => $SupplySlipDetail['seri_no'],
                    'inventory_unit_id'  => $SupplySlipDetail['inventory_unit_id'],
                    'inventory_unit_num' => $SupplySlipDetail['inventory_unit_num'],
                    'memo'               => $SupplySlipDetail['memo'],
                    'sort'               => $sort,
                    'created_user_id'    => $user_info_id,
                    'created'            => Carbon::now(),
                    'modified_user_id'   => $user_info_id,
                    'modified'           => Carbon::now(),
                ];

                $sort ++;
            }

            if(!empty($supply_slip_detail)) {

                DB::table('supply_slip_details')->insert($supply_slip_detail);
            }

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./SupplySlipIndex');
    }

    /**
     * 仕入登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SupplySlip.create');
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxChangeProductId(Request $request)
    {
        // 伝票番号を取得
        $slip_num = $request->slip_num;

        // 製品IDを取得
        $product_id = $request->selected_product_id;

        if (!empty($product_id)) {

            // 規格情報取得
            $StandardList = Standard::where([
                ['product_id', $product_id],
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 製品の税率情報を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.tax_id  AS tax_id',
                'Tax.name        AS tax_name'
            )
                ->join('taxes AS Tax', function ($join) {
                    $join->on('Tax.id', '=', 'Product.tax_id');
                })
                ->where([
                    ['Product.id', '=', $product_id],
                    ['Product.active', '=', '1'],
                ])->first();

            $tax_id   = $productList->tax_id;
            $tax_name = $productList->tax_name;

        } else {
            $tax_id   = 0;
            $tax_name = '';
        }

        // 規格のSELECTを形成
        $ajaxHtml = '';
        $ajaxHtml .= "<select class='form-control' id='standard_id_{$slip_num}' name='data[SupplySlip][standard_id][{$slip_num}]'>";
        $ajaxHtml .= "    <option value='0'>-</option>";
                        if(!empty($StandardList)) {
                           foreach ($StandardList as $Standards){
        $ajaxHtml .= "    <option value='{$Standards->id}'>{$Standards->name}</option>";
                           }
                        }
        $ajaxHtml .= "</select>";

        $returnArray = array($ajaxHtml, $tax_id, $tax_name);
        return $returnArray;

    }

    /**
     * 仕入企業ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSupplyCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $supplyCompanyList = DB::table('supply_companies AS SupplyCompany')
            ->select(
                'SupplyCompany.name  AS supply_company_name'
            )->where([
                    ['SupplyCompany.active', '=', '1']
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SupplyCompany.name', 'like', "%{$input_text}%")
                ->orWhere('SupplyCompany.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($supplyCompanyList)) {

                foreach ($supplyCompanyList as $supply_company_val) {

                    array_push($auto_complete_array, $supply_company_val->supply_company_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 仕入先企業更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSupplyCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id   = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $supplyCompanyList = DB::table('supply_companies AS SupplyCompany')
            ->select(
                'SupplyCompany.code  AS code',
                'SupplyCompany.id    AS id',
                'SupplyCompany.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SupplyCompany.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SupplyCompany.name', 'like', $input_name);
            })
            ->first();

            if (!empty($supplyCompanyList)) {
                $output_code = $supplyCompanyList->code;
                $output_id   = $supplyCompanyList->id;
                $output_name = $supplyCompanyList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 仕入店舗ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSupplyShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $supplyShopList = DB::table('supply_shops AS SupplyShop')
            ->select(
                'SupplyShop.name  AS supply_shop_name'
            )->where([
                    ['SupplyShop.active', '=', '1']
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SupplyShop.name', 'like', "%{$input_text}%")
                ->orWhere('SupplyShop.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($supplyShopList)) {

                foreach ($supplyShopList as $supply_shop_val) {

                    array_push($auto_complete_array, $supply_shop_val->supply_shop_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 仕入先店舗更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSupplyShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id   = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $supplyShopList = DB::table('supply_shops AS SupplyShop')
            ->select(
                'SupplyShop.code  AS code',
                'SupplyShop.id    AS id',
                'SupplyShop.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SupplyShop.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SupplyShop.name', 'like', $input_name);
            })
            ->first();

            if (!empty($supplyShopList)) {
                $output_code = $supplyShopList->code;
                $output_id   = $supplyShopList->id;
                $output_name = $supplyShopList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteProduct(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.name  AS product_name'
            )->where([
                    ['Product.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Product.name', 'like', "%{$input_text}%")
                ->orWhere('Product.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($productList)) {

                foreach ($productList as $product_val) {

                    array_push($auto_complete_array, $product_val->product_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetProduct(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $product_code = $input_text;
            $product_name = null;
        } else {
            $product_code = null;
            $product_name = $input_text;
        }

        // 初期化
        $output_product_code        = null;
        $output_product_id          = null;
        $output_product_name        = null;
        $output_tax_id              = null;
        $output_tax_name            = null;
        $output_unit_id             = null;
        $output_unit_name           = null;
        $output_inventory_unit_id   = null;
        $output_inventory_unit_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.code       AS code',
                'Product.id         AS id',
                'Product.name       AS product_name',
                'Tax.id             AS tax_id',
                'Tax.name           AS tax_name',
                'Unit.id            AS unit_id',
                'Unit.name          AS unit_name',
                'InventoryUnit.id   AS inventory_unit_id',
                'InventoryUnit.name AS inventory_unit_name'
            )->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            }
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            }
            )->join('units AS InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'Product.inventory_unit_id');
            })
            ->if(!empty($product_code), function ($query) use ($product_code) {
                return $query->where('Product.code', '=', $product_code);
            })
            ->if(!empty($product_name), function ($query) use ($product_name) {
                return $query->where('Product.name', 'like', $product_name);
            })
            ->first();

            if (!empty($productList)) {
                $output_product_code        = $productList->code;
                $output_product_id          = $productList->id;
                $output_product_name        = $productList->product_name;
                $output_tax_id              = $productList->tax_id;
                $output_tax_name            = $productList->tax_name;
                $output_unit_id             = $productList->unit_id;
                $output_unit_name           = $productList->unit_name;
                $output_inventory_unit_id   = $productList->inventory_unit_id;
                $output_inventory_unit_name = $productList->inventory_unit_name;
            }
        }

        $returnArray = array(
            $output_product_code,
            $output_product_id,
            $output_product_name,
            $output_tax_id,
            $output_tax_name,
            $output_unit_id,
            $output_unit_name,
            $output_inventory_unit_id,
            $output_inventory_unit_name
        );

        return json_encode($returnArray);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStandard(Request $request)
    {
        // 入力された値を取得
        $productId  = $request->productId;
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $standardList = DB::table('standards AS Standard')
            ->select(
                'Standard.name  AS name'
            )->where([
                    ['Standard.product_id', '=', $productId],
                    ['Standard.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Standard.name', 'like', "%{$input_text}%")
                ->orWhere('Standard.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($standardList)) {

                foreach ($standardList as $standard_val) {

                    array_push($auto_complete_array, $standard_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStandard(Request $request)
    {
        // 入力された値を取得
        $productId  = $request->productId;
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $productList = DB::table('standards AS Standard')
            ->select(
                'Standard.code  AS code',
                'Standard.id    AS id',
                'Standard.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Standard.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Standard.name', 'like', $input_name);
            })->where([
                ['Standard.product_id', '=', $productId],
                ['Standard.active', '=', '1'],
            ])->first();

            if (!empty($productList)) {
                $output_code = $productList->code;
                $output_id   = $productList->id;
                $output_name = $productList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteQuality(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $qualityList = DB::table('qualities AS Quality')
            ->select(
                'Quality.name  AS name'
            )->where([
                    ['Quality.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Quality.name', 'like', "%{$input_text}%")
                ->orWhere('Quality.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($qualityList)) {

                foreach ($qualityList as $quality_val) {

                    array_push($auto_complete_array, $quality_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetQuality(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $qualityList = DB::table('qualities AS Quality')
            ->select(
                'Quality.code  AS code',
                'Quality.id    AS id',
                'Quality.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Quality.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Quality.name', 'like', $input_name);
            })
            ->first();

            if (!empty($qualityList)) {
                $output_code = $qualityList->code;
                $output_id   = $qualityList->id;
                $output_name = $qualityList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteOriginArea(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $originAreaList = DB::table('origin_areas AS OriginArea')
            ->select(
                'OriginArea.name  AS name'
            )->where([
                    ['OriginArea.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('OriginArea.name', 'like', "%{$input_text}%")
                ->orWhere('OriginArea.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($originAreaList)) {

                foreach ($originAreaList as $origin_area_val) {

                    array_push($auto_complete_array, $origin_area_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetOriginArea(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $originAreaList = DB::table('origin_areas AS OriginArea')
            ->select(
                'OriginArea.id    AS id',
                'OriginArea.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('OriginArea.id', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('OriginArea.name', 'like', $input_name);
            })
            ->first();

            if (!empty($originAreaList)) {
                $output_code = $originAreaList->id;
                $output_id   = $originAreaList->id;
                $output_name = $originAreaList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $staffList = DB::table('staffs AS Staff')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS name')
            ->where([
                    ['Staff.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "%'.$input_text.'%"')
                ->orWhereRaw('CONCAT(Staff.yomi_sei,Staff.yomi_mei) like "%'.$input_text.'%"');
            })
            ->get();

            if (!empty($staffList)) {

                foreach ($staffList as $staff_val) {

                    array_push($auto_complete_array, $staff_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 担当者TBL取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.code  AS code',
                'Staff.id    AS id'
            )
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS name')
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Staff.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->whereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "'.$input_name.'"');
            })
            ->first();

            if (!empty($staffList)) {
                $output_code = $staffList->code;
                $output_id   = $staffList->id;
                $output_name = $staffList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteDelivery(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $deliveryList = DB::table('deliverys AS Delivery')
            ->select(
                'Delivery.name  AS name'
            )->where([
                    ['Delivery.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Delivery.name', 'like', "%{$input_text}%")
                ->orWhere('Delivery.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($deliveryList)) {

                foreach ($deliveryList as $delivery_val) {

                    array_push($auto_complete_array, $delivery_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetDelivery(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品 DB取得
            $deliveryList = DB::table('deliverys AS Delivery')
            ->select(
                'Delivery.id    AS id',
                'Delivery.code  AS code',
                'Delivery.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Delivery.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Delivery.name', 'like', $input_name);
            })
            ->first();

            if (!empty($deliveryList)) {
                $output_code = $deliveryList->code;
                $output_id   = $deliveryList->id;
                $output_name = $deliveryList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 仕入伝票の登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerSupplySlips(Request $request)
    {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SupplySlipData = $request->data['SupplySlip'];
            $SupplySlipDetailData = $request->data['SupplySlipDetail'];

            // 値がNULLのところを初期化
            if(empty($SupplySlipData['supply_shop_id'])) $SupplySlipData['supply_shop_id'] = 0;
            if(empty($SupplySlipData['delivery_id'])) $SupplySlipData['delivery_id'] = 0;
            if(empty($SupplySlipData['delivery_price'])) $SupplySlipData['delivery_price'] = 0;
            if(empty($SupplySlipData['adjust_price'])) $SupplySlipData['adjust_price'] = 0;

            // supply_slipsを登録する
            $SupplySlip = new SupplySlip;
            $SupplySlip->date               = $SupplySlipData['supply_date'];          // 日付
            $SupplySlip->delivery_date      = $SupplySlipData['delivery_date'];        // 納品日
            $SupplySlip->supply_company_id  = $SupplySlipData['supply_company_id'];    // 仕入先ID
            $SupplySlip->supply_shop_id     = $SupplySlipData['supply_shop_id'];       // 仕入先店舗ID
            $SupplySlip->delivery_id        = $SupplySlipData['delivery_id'];          // 配送ID
            $SupplySlip->notax_sub_total_8  = $SupplySlipData['notax_sub_total_8'];    // 8%課税対象額
            $SupplySlip->notax_sub_total_10 = $SupplySlipData['notax_sub_total_10'];   // 10%課税対象額
            $SupplySlip->notax_sub_total    = $SupplySlipData['notax_sub_total'];      // 税抜合計額
            $SupplySlip->tax_total_8        = $SupplySlipData['tax_total_8'];          // 8%課税対象額
            $SupplySlip->tax_total_10       = $SupplySlipData['tax_total_10'];         // 10%課税対象額
            $SupplySlip->tax_total          = $SupplySlipData['tax_total'];            // 税抜合計額
            $SupplySlip->sub_total_8        = $SupplySlipData['sub_total_8'];          // 8%合計額
            $SupplySlip->sub_total_10       = $SupplySlipData['sub_total_10'];         // 10%合計額
            $SupplySlip->delivery_price     = $SupplySlipData['delivery_price'];       // 合計額
            $SupplySlip->sub_total          = $SupplySlipData['sub_total'];            // 配送額
            $SupplySlip->adjust_price       = $SupplySlipData['adjust_price'];         // 調整額
            $SupplySlip->total              = $SupplySlipData['total'];                // 合計額
            $SupplySlip->remarks            = $SupplySlipData['remarks'];              // 備考
            $SupplySlip->supply_submit_type = $SupplySlipData['supply_submit_type'];   // 登録タイプ
            $SupplySlip->sort               = 100;                                   // ソート
            $SupplySlip->created_user_id    = $user_info_id;                         // 作成者ユーザーID
            $SupplySlip->created            = Carbon::now();                         // 作成時間
            $SupplySlip->modified_user_id   = $user_info_id;                         // 更新者ユーザーID
            $SupplySlip->modified           = Carbon::now();                         // 更新時間

            $SupplySlip->save();

            // 作成したIDを取得する
            $supply_slip_new_id = $SupplySlip->id;

            $supply_slip_detail = array();
            $sort = 0;

            foreach($SupplySlipDetailData as $SupplySlipDetail){

                // 値がNULLのところを初期化
                if (empty($SupplySlipData['standard_id'])) $SupplySlipData['standard_id'] = 0;
                if (empty($SupplySlipData['quality_id'])) $SupplySlipData['quality_id'] = 0;
                if (empty($SupplySlipData['origin_area_id'])) $SupplySlipData['origin_area_id'] = 0;

                $supply_slip_detail[] = [
                    'supply_slip_id'     => $supply_slip_new_id,
                    'product_id'         => $SupplySlipDetail['product_id'],
                    'standard_id'        => $SupplySlipDetail['standard_id'],
                    'quality_id'         => $SupplySlipDetail['quality_id'],
                    'unit_price'         => $SupplySlipDetail['unit_price'],
                    'unit_num'           => $SupplySlipDetail['unit_num'],
                    'notax_price'        => $SupplySlipDetail['notax_price'],
                    'unit_id'            => $SupplySlipDetail['unit_id'],
                    'origin_area_id'     => $SupplySlipDetail['origin_area_id'],
                    'staff_id'           => $SupplySlipDetail['staff_id'],
                    'seri_no'            => $SupplySlipDetail['seri_no'],
                    'inventory_unit_id'  => $SupplySlipDetail['inventory_unit_id'],
                    'inventory_unit_num' => $SupplySlipDetail['inventory_unit_num'],
                    'memo'               => $SupplySlipDetail['memo'],
                    'sort'               => $sort,
                    'created_user_id'    => $user_info_id,
                    'created'            => Carbon::now(),
                    'modified_user_id'   => $user_info_id,
                    'modified'           => Carbon::now(),
                ];

                $sort ++;
            }

            if(!empty($supply_slip_detail)) {

                DB::table('supply_slip_details')->insert($supply_slip_detail);
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./SupplySlipIndex');
    }

    /**
     * 伝票新規追加　登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddSlip(Request $request)
    {
        // 伝票NOを取得
        $slip_num = $request->slip_num;
        if(empty($slip_num)) $slip_num = 1;

        $tabInitialNum = intval(9*$slip_num + 3);

        // 追加伝票形成
        $ajaxHtml1 = '';
        $ajaxHtml1 .= " <tr id='slip-partition-".$slip_num."' class='partition-area'>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-upper-".$slip_num."'>";
        $ajaxHtml1 .= "     <td class='width-10' id='product-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='product_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][product_id]'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-20'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='product_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][product_text]' placeholder='製品欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td class='width-15' colspan='2'>";
        //$ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_price_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_price]'  onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 1)."'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='inventory_unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_num]' tabindex='".($tabInitialNum + 1)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_text]' placeholder='個数欄' readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='inventory_unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_id]' value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-10' id='origin-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='origin_area_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][origin_area_id]' tabindex='".($tabInitialNum + 4)."' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-20'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='origin_area_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][origin_area_text]' placeholder='産地欄' readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-15'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='tax_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][tax_text]' placeholder='税率欄'  readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='tax_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][tax_id]'  value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td rowspan='4' class='width-5'>";
        $ajaxHtml1 .= "         <button id='remove-slip-btn' type='button' class='btn remove-slip-btn btn-secondary' onclick='javascript:removeSlip(".$slip_num.") '>削除</button>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-middle-".$slip_num."'>";
        $ajaxHtml1 .= "     <td id='standard-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='standard_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][standard_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='standard_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][standard_text]' placeholder='規格欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_num]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 2)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_text]' readonly>";
        //$ajaxHtml1 .= "         <input type='hidden' id='unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_num]' tabindex='".($tabInitialNum + 2)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_text]' placeholder='個数欄' readonly>";
        //$ajaxHtml1 .= "         <input type='hidden' id='inventory_unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_num]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 2)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_text]' placeholder='数量欄' readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_id]' value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-10' id='staff-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='staff_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][staff_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-20'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='staff_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][staff_text]' placeholder='担当欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='seri_no_".$slip_num."'  name='data[SupplySlipDetail][".$slip_num."][seri_no]' placeholder='セリNO欄' tabindex='".($tabInitialNum + 6)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-lower-".$slip_num."'>";
        $ajaxHtml1 .= "     <td id='quality-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='quality_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][quality_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='quality_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][quality_text]' placeholder='品質欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_num]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 3)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_text]' placeholder='数量欄' readonly>";
        //$ajaxHtml1 .= "         <input type='hidden' id='unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td colspan='2'>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='notax_price_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][notax_price]'  value='0' readonly>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td colspan='2'>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_num_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_num]' tabindex='".($tabInitialNum + 5)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' id='inventory_unit_text_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_text]' readonly='readonly' class='form-control'>";
        //$ajaxHtml1 .= "         <input type='hidden' id='inventory_unit_id_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][inventory_unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-15' colspan='2'>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_price_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][unit_price]'  onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 3)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "       <td colspan='3'>";
        $ajaxHtml1 .= "           <input type='text' class='form-control' id='memo_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][memo]' tabindex='".($tabInitialNum + 7)."' placeholder='摘要欄'>";
        $ajaxHtml1 .= "       </td>";
        $ajaxHtml1 .= " </tr>";
        //$ajaxHtml1 .= " <tr id='slip-most-lower-".$slip_num."'>";
        //$ajaxHtml1 .= "     <td colspan='7'>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='memo_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][memo]' tabindex='".($tabInitialNum + 7)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-most-lower-".$slip_num."'>";
        $ajaxHtml1 .= "     <td>小計</td>";
        $ajaxHtml1 .= "     <td colspan='3'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='notax_price_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][notax_price]' value='0' readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <hr/>";

        //-------------------------------
        // AutoCompleteの要素は別で形成する
        //-------------------------------
        // 製品ID
        $autoCompleteProduct = "<input type='text' class='form-control product_code_input' id='product_code_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][product_code]' tabindex='".$tabInitialNum."''>";
        // 規格ID
        $autoCompleteStandard = "<input type='text' class='form-control standard_code_input' id='standard_code_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][standard_code]'>";
        // 品質ID
        $autoCompleteQuality = "<input type='text' class='form-control quality_code_input' id='quality_code_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][quality_code]'>";
        // 産地
        $autoCompleteOrigin = "<input type='text' class='form-control origin_area_code_input' id='origin_area_code_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][origin_area_code]' tabindex='".($tabInitialNum + 4)."'>";
        // 担当
        $autoCompleteStaff = "<input type='text' class='form-control staff_code_input' id='staff_code_".$slip_num."' name='data[SupplySlipDetail][".$slip_num."][staff_code]' tabindex='".($tabInitialNum + 5)."'>";

        $slip_num = intval($slip_num) + 1;

        $returnArray = array($slip_num, $ajaxHtml1, $autoCompleteProduct, $autoCompleteStandard, $autoCompleteQuality, $autoCompleteOrigin, $autoCompleteStaff);


        return $returnArray;
    }
}

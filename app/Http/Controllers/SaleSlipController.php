<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use App\SaleSlip;
use App\SaleSlipDetail;
use App\SupplySlipDetail;
use App\InventoryManage;
use Carbon\Carbon;

class SaleSlipController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->SaleSlip         = new SaleSlip;
        $this->SaleSlipDetail   = new SaleSlipDetail;
        $this->SupplySlipDetail = new SupplySlipDetail;
    }

    /**
     * 売上一覧
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
            $search_action = '../SaleSlipIndex';
        } else {
            $search_action = './SaleSlipIndex';
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
            // トップページから遷移してくる場合があるので、条件判定
            $request_submit_type = $request->input('sale_submit_type');
            if (!empty($request_submit_type)) {
                $condition_submit_type = $request_submit_type;
            } else {
                $condition_submit_type = $request->session()->get('condition_submit_type');
            }

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type     = $request->data['SaleSlip']['date_type'];
                $condition_company_code  = $request->data['SaleSlip']['sale_company_code'];
                $condition_company_id    = $request->data['SaleSlip']['sale_company_id'];
                $condition_company_text  = $request->data['SaleSlip']['sale_company_text'];
                $condition_shop_code     = $request->data['SaleSlip']['sale_shop_code'];
                $condition_shop_id       = $request->data['SaleSlip']['sale_shop_id'];
                $condition_shop_text     = $request->data['SaleSlip']['sale_shop_text'];
                $condition_product_code  = $request->data['SaleSlipDetail']['product_code'];
                $condition_product_id    = $request->data['SaleSlipDetail']['product_id'];
                $condition_product_text  = $request->data['SaleSlipDetail']['product_text'];
                $condition_submit_type   = isset($request->data['SaleSlip']['sale_submit_type']) ? $request->data['SaleSlip']['sale_submit_type'] : 0;

                // 日付の設定
                $condition_date_from     = $request->data['SaleSlip']['sale_date_from'];
                $condition_date_to       = $request->data['SaleSlip']['sale_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

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

            // sale_slip_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {

                $product_sub_query = DB::table('sale_slip_details as SubTable')
                ->select('SubTable.sale_slip_id AS sale_slip_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->groupBy('SubTable.sale_slip_id');
            }

            // 売上一覧を取得
            $saleSlipList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.id                  AS sale_slip_id',
                'SaleSlip.delivery_price      AS delivery_price',
                'SaleSlip.adjust_price        AS adjust_price',
                'SaleSlip.notax_sub_total     AS notax_sub_total',
                'SaleSlip.total               AS sale_slip_total',
                'SaleSlip.sale_submit_type    AS sale_submit_type',
                'SaleCompany.code             AS sale_company_code',
                'SaleCompany.name             AS sale_company_name',
                'SaleShop.name                AS sale_shop_name'
            )
            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y/%m/%d")          AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y/%m/%d") AS sale_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.modified, "%m-%d %H:%i")   AS sale_slip_modified')
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SaleSlip.sale_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SaleSlip.sale_shop_id', '=', $condition_shop_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SaleSlipDetail'), 'SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SaleSlip.sale_submit_type', '=', $condition_submit_type);
            })
            ->where('SaleSlip.active', '=', '1')
            ->orderBy('SaleSlip.date', 'desc')->paginate(10);

            // 伝票詳細を取得
            $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
            ->select(
                'SaleSlip.id                  AS sale_slip_id',
                'SaleSlip.total               AS sale_slip_total',
                'SaleSlip.sale_submit_type  AS sale_submit_type',
                'SaleCompany.code             AS sale_company_code',
                'SaleCompany.name             AS sale_company_name',
                'Product.code                   AS product_code',
                'Product.name                   AS product_name',
                'Product.tax_id                 AS product_tax_id',
                'Standard.name                  AS standard_name',
                'SaleSlipDetail.id            AS sale_slip_detail_id',
                'SaleSlipDetail.unit_price    AS sale_slip_detail_unit_price',
                'SaleSlipDetail.unit_num      AS sale_slip_detail_unit_num',
                'Unit.name                      AS unit_name',
            )
            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y/%m/%d")                AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y/%m/%d") AS sale_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.modified, "%m-%d %H:%i")      AS sale_slip_modified')

            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei)            AS staff_name')

            ->join('sale_slips as SaleSlip', function ($join) {
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id')
                ->where('SaleSlip.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->leftJoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SaleSlipDetail.standard_id')
                ->where('Standard.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SaleSlipDetail.staff_id')
                ->where('Staff.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SaleSlipDetail.inventory_unit_id')
                ->where('InventoryUnit.active', '=', true);
            })
            ->leftJoin('sale_companies as SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                ->where('SaleCompany.active', '=', true);
            })
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $sale_slip_detail_arr = array();


            // 伝票詳細で取得したDBをループ
            foreach($SaleSlipDetailList as $SaleSlipDetails){

                if(!isset($sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id])){
                    $sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id] = 0;
                }

                $sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id] += 1;

                $sale_slip_detail_arr[$SaleSlipDetails->sale_slip_id][] = [

                    'product_code'                => $SaleSlipDetails->product_code,
                    'product_name'                => $SaleSlipDetails->product_name,
                    'product_tax_id'              => $SaleSlipDetails->product_tax_id,
                    'standard_name'               => $SaleSlipDetails->standard_name,
                    'sale_slip_detail_id'         => $SaleSlipDetails->sale_slip_detail_id,
                    'sale_slip_detail_unit_price' => $SaleSlipDetails->sale_slip_detail_unit_price,
                    'sale_slip_detail_unit_num'   => $SaleSlipDetails->sale_slip_detail_unit_num,
                    'unit_name'                   => $SaleSlipDetails->unit_name,
                    'staff_name'                  => $SaleSlipDetails->staff_name,
                ];
            }

            // 対象日付のチェック
            $check_str_slip_date = "";
            $check_str_deliver_date = "";
            if($condition_date_type == 1) $check_str_slip_date = "checked";
            else  $check_str_deliver_date = "checked";

        } catch (\Exception $e) {

            dd($e);

            return view('SaleSlip.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SaleSlip.index')->with([
            "search_action"              => $search_action,
            "check_str_slip_date"        => $check_str_slip_date,
            "check_str_deliver_date"     => $check_str_deliver_date,
            "condition_date_from"        => $condition_date_from,
            "condition_date_to"          => $condition_date_to,
            "condition_company_code"     => $condition_company_code,
            "condition_company_id"       => $condition_company_id,
            "condition_company_text"     => $condition_company_text,
            "condition_shop_code"        => $condition_shop_code,
            "condition_shop_id"          => $condition_shop_id,
            "condition_shop_text"        => $condition_shop_text,
            "condition_product_code"     => $condition_product_code,
            "condition_product_id"       => $condition_product_id,
            "condition_product_text"     => $condition_product_text,
            "condition_submit_type"      => $condition_submit_type,
            "saleSlipList"               => $saleSlipList,
            "SaleSlipDetailList"         => $SaleSlipDetailList,
            "sale_slip_detail_arr"       => $sale_slip_detail_arr,
            "sale_slip_detail_count_arr" => $sale_slip_detail_count_arr
        ]);
    }

    /**
     * 売上編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($sale_slip_id)
    {
        // activeの変数を格納
        $this_active = 1;

        //------------------
        // 売上伝票取得
        //------------------
        $SaleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id                  AS sale_slip_id',
            'SaleSlip.notax_sub_total_8   AS notax_sub_total_8',
            'SaleSlip.notax_sub_total_10  AS notax_sub_total_10',
            'SaleSlip.notax_sub_total     AS notax_sub_total',
            'SaleSlip.tax_total_8         AS tax_total_8',
            'SaleSlip.tax_total_10        AS tax_total_10',
            'SaleSlip.tax_total           AS tax_total',
            'SaleSlip.sub_total_8         AS sub_total_8',
            'SaleSlip.sub_total_10        AS sub_total_10',
            'SaleSlip.sub_total           AS sub_total',
            'SaleSlip.delivery_price      AS delivery_price',
            'SaleSlip.adjust_price        AS adjust_price',
            'SaleSlip.total               AS total',
            'SaleSlip.remarks             AS remarks',
            'SaleSlip.sale_submit_type  AS sale_submit_type',
            'SaleCompany.code             AS sale_company_code',
            'SaleCompany.id               AS sale_company_id',
            'SaleCompany.name             AS sale_company_name',
            'SaleShop.code                AS sale_shop_code',
            'SaleShop.id                  AS sale_shop_id',
            'SaleShop.name                AS sale_shop_name',
            'Delivery.code                  AS delivery_code',
            'Delivery.id                    AS delivery_id',
            'Delivery.name                  AS delivery_name'
        )
        ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y-%m-%d") AS sale_slip_sale_date')
        ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y-%m-%d") AS sale_slip_delivery_date')
        ->join('sale_companies as SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                 ->where('SaleCompany.active', '=', true);
        })
        ->leftJoin('sale_shops as SaleShop', function ($join) {
            $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id')
                 ->where('SaleShop.active', '=', true);
        })
        ->leftJoin('deliverys as Delivery', function ($join) {
            $join->on('Delivery.id', '=', 'SaleSlip.delivery_id')
                 ->where('Delivery.active', '=', true);
        })
        ->where([
            ['SaleSlip.id', '=', $sale_slip_id],
            ['SaleSlip.active', '=', $this_active],
        ])
        ->first();

        //------------------
        // 売上伝票詳細取得
        //------------------

        // inventory_managesのサブクエリを作成
        $inventory_sub_query = DB::table('inventory_manages as SubInventoryManage')
            ->select(
                'SubInventoryManage.sale_detail_slip_id   AS sale_detail_slip_id'
            )
            ->selectRaw('count(SubInventoryManage.unit_num) AS unit_num_count')
            ->selectRaw('sum(SubInventoryManage.unit_num) AS unit_num_sum')
            ->join('sale_slip_details as SubSaleSlipDetail', function ($join) {
                $join->on('SubSaleSlipDetail.id', '=', 'SubInventoryManage.sale_detail_slip_id');
            })
            ->whereRaw('SubSaleSlipDetail.sale_slip_id =' . $sale_slip_id)
            ->groupBy('SubInventoryManage.sale_detail_slip_id');

        // メインSQL
        $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
        ->select(
            'SaleSlipDetail.id                 AS sale_slip_detail_id',
            'SaleSlipDetail.unit_price         AS unit_price',
            'SaleSlipDetail.unit_num           AS unit_num',
            'SaleSlipDetail.notax_price        AS notax_price',
            'SaleSlipDetail.seri_no            AS seri_no',
            'SaleSlipDetail.inventory_unit_num AS inventory_unit_num',
            'SaleSlipDetail.memo               AS memo',
            'SaleSlipDetail.sort               AS sort',
            'Product.code                      AS product_code',
            'Product.id                        AS product_id',
            'Product.name                      AS product_name',
            'Tax.id                            AS tax_id',
            'Tax.name                          AS tax_name',
            'Standard.code                     AS standard_code',
            'Standard.id                       AS standard_id',
            'Standard.name                     AS standard_name',
            'Quality.code                      AS quality_code',
            'Quality.id                        AS quality_id',
            'Quality.name                      AS quality_name',
            'Unit.id                           AS unit_id',
            'Unit.name                         AS unit_name',
            'OriginArea.id                     AS origin_area_id',
            'OriginArea.name                   AS origin_area_name',
            'Staff.code                        AS staff_code',
            'Staff.id                          AS staff_id',
            'InventoryManage.unit_num_count    AS unit_num_count',
            'InventoryManage.unit_num_sum      AS unit_num_sum',
            'InventoryUnit.id                  AS inventory_unit_id',
            'InventoryUnit.name                AS inventory_unit_name'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                 ->where('Product.active', '=', true);
        })
        ->join('taxes as Tax', function ($join) {
            $join->on('Tax.id', '=', 'Product.tax_id')
                 ->where('Tax.active', '=', true);
        })
        ->leftJoin('standards as Standard', function ($join) {
            $join->on('Standard.id', '=', 'SaleSlipDetail.standard_id')
                 ->where('Standard.active', '=', true);
        })
        ->leftJoin('qualities as Quality', function ($join) {
            $join->on('Quality.id', '=', 'SaleSlipDetail.quality_id')
                 ->where('Quality.active', '=', true);
        })
        ->join('units as Unit', function ($join) {
            $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                 ->where('Unit.active', '=', true);
        })
        ->leftJoin('units as InventoryUnit', function ($join) {
            $join->on('InventoryUnit.id', '=', 'SaleSlipDetail.inventory_unit_id')
                 ->where('InventoryUnit.active', '=', true);
        })
        ->leftJoin('origin_areas as OriginArea', function ($join) {
            $join->on('OriginArea.id', '=', 'SaleSlipDetail.origin_area_id')
                 ->where('OriginArea.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'SaleSlipDetail.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->leftjoin(DB::raw('('. $inventory_sub_query->toSql() .') as InventoryManage'), function ($join) {
            $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id');
        })
        ->where([
            ['SaleSlipDetail.sale_slip_id','=', $sale_slip_id],
            ['SaleSlipDetail.active','=', $this_active]
        ])
        ->get();

        //------------------
        // 在庫管理
        //------------------
        $inventoryManageList = DB::table('sale_slip_details as SaleSlipDetail')
            ->select(
                'SaleSlipDetail.id                      AS sale_detail_slip_id',
                'SaleSlipDetail.sort                    AS sale_slip_detail_sort',
                'InventoryManage.supply_detail_slip_id  AS supply_detail_slip_id',
                'InventoryManage.unit_num               AS unit_num'
            )
            ->selectRaw(
                'COALESCE(InventoryManage.sort, 0) AS inventory_manage_sort'
            )
            ->leftJoin('inventory_manages as InventoryManage', function ($join) {
                $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id')
                     ->where('InventoryManage.active', '=', true);
            })
            ->where('SaleSlipDetail.sale_slip_id', '=', $sale_slip_id)
            ->orderByRaw("SaleSlipDetail.sort asc, InventoryManage.sort asc")
            ->get();

        // 在庫管理しやすいようにする
        $inventoryManageArr = array();
        foreach($inventoryManageList as $inventoryManageVal) {

            $sale_slip_detail_sort = $inventoryManageVal->sale_slip_detail_sort;
            $inventory_manage_sort = $inventoryManageVal->inventory_manage_sort;

            $inventoryManageArr[$sale_slip_detail_sort][$inventory_manage_sort] = [
                'sale_detail_slip_id'   => $inventoryManageVal->sale_detail_slip_id,
                'supply_detail_slip_id' => $inventoryManageVal->supply_detail_slip_id,
                'unit_num'              => $inventoryManageVal->unit_num,
            ];
        }

        return view('SaleSlip.edit')->with([
            "sale_slip_id"        => $sale_slip_id,
            "SaleSlipList"        => $SaleSlipList,
            "SaleSlipDetailList"  => $SaleSlipDetailList,
            "inventoryManageArr"  => $inventoryManageArr,
        ]);
    }

    /**
     * 編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request)
    {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SaleSlipData = $request->data['SaleSlip'];
            $SaleSlipDetailData = $request->data['SaleSlipDetail'];

            // 値がNULLのところを初期化
            if(empty($SaleSlipData['sale_shop_id'])) $SaleSlipData['sale_shop_id'] = 0;
            if(empty($SaleSlipData['delivery_id'])) $SaleSlipData['delivery_id'] = 0;
            if(empty($SaleSlipData['delivery_price'])) $SaleSlipData['delivery_price'] = 0;
            if(empty($SaleSlipData['adjust_price'])) $SaleSlipData['adjust_price'] = 0;

            // sale_slipsを登録する
            $SaleSlip = \App\SaleSlip::find($SaleSlipData['id']);
            $SaleSlip->date               = $SaleSlipData['sale_date'];            // 日付
            $SaleSlip->delivery_date      = $SaleSlipData['delivery_date'];        // 納品日
            $SaleSlip->sale_company_id    = $SaleSlipData['sale_company_id'];      // 売上先ID
            $SaleSlip->sale_shop_id       = $SaleSlipData['sale_shop_id'];         // 売上先店舗ID
            $SaleSlip->delivery_id        = $SaleSlipData['delivery_id'];          // 配送ID
            $SaleSlip->notax_sub_total_8  = $SaleSlipData['notax_sub_total_8'];    // 8%課税対象額
            $SaleSlip->notax_sub_total_10 = $SaleSlipData['notax_sub_total_10'];   // 10%課税対象額
            $SaleSlip->notax_sub_total    = $SaleSlipData['notax_sub_total'];      // 税抜合計額
            $SaleSlip->tax_total_8        = $SaleSlipData['tax_total_8'];          // 8%課税対象額
            $SaleSlip->tax_total_10       = $SaleSlipData['tax_total_10'];         // 10%課税対象額
            $SaleSlip->tax_total          = $SaleSlipData['tax_total'];            // 税抜合計額
            $SaleSlip->sub_total_8        = $SaleSlipData['sub_total_8'];          // 8%合計額
            $SaleSlip->sub_total_10       = $SaleSlipData['sub_total_10'];         // 10%合計額
            $SaleSlip->delivery_price     = $SaleSlipData['delivery_price'];       // 合計額
            $SaleSlip->sub_total          = $SaleSlipData['sub_total'];            // 配送額
            $SaleSlip->adjust_price       = $SaleSlipData['adjust_price'];         // 調整額
            $SaleSlip->total              = $SaleSlipData['total'];                // 合計額
            $SaleSlip->remarks            = $SaleSlipData['remarks'];              // 備考
            $SaleSlip->sale_submit_type   = $SaleSlipData['sale_submit_type'];     // 登録タイプ
            $SaleSlip->modified_user_id   = $user_info_id;                         // 更新者ユーザーID
            $SaleSlip->modified           = Carbon::now();                         // 更新時間

            $SaleSlip->save();

            // 作成したIDを取得する
            $sale_slip_new_id = $SaleSlip->id;

            // 伝票詳細を削除
            \App\SaleSlipDetail::where('sale_slip_id', $SaleSlipData['id'])->delete();

            $sale_slip_detail = array();
            $sort = 0;

            $saleSlipDetailIds = array();
            foreach($SaleSlipDetailData as $SaleSlipDetail){

                // 値がNULLのところを初期化
                if (empty($SaleSlipDetail['standard_id'])) $SaleSlipDetail['standard_id'] = 0;
                if (empty($SaleSlipDetail['quality_id'])) $SaleSlipDetail['quality_id'] = 0;
                if (empty($SaleSlipDetail['origin_area_id'])) $SaleSlipDetail['origin_area_id'] = 0;
                if (empty($SaleSlipDetail['seri_no'])) $SaleSlipDetail['seri_no'] = 0;

                $sale_slip_detail[] = [
                    'sale_slip_id'       => $sale_slip_new_id,
                    'product_id'         => $SaleSlipDetail['product_id'],
                    'standard_id'        => $SaleSlipDetail['standard_id'],
                    'quality_id'         => $SaleSlipDetail['quality_id'],
                    'unit_price'         => $SaleSlipDetail['unit_price'],
                    'unit_num'           => $SaleSlipDetail['unit_num'],
                    'notax_price'        => $SaleSlipDetail['notax_price'],
                    'unit_id'            => $SaleSlipDetail['unit_id'],
                    'origin_area_id'     => $SaleSlipDetail['origin_area_id'],
                    'staff_id'           => $SaleSlipDetail['staff_id'],
                    'seri_no'            => $SaleSlipDetail['seri_no'],
                    'inventory_unit_id'  => $SaleSlipDetail['inventory_unit_id'],
                    'inventory_unit_num' => $SaleSlipDetail['inventory_unit_num'],
                    'memo'               => $SaleSlipDetail['memo'],
                    'sort'               => $sort,
                    'created_user_id'    => $user_info_id,
                    'created'            => Carbon::now(),
                    'modified_user_id'   => $user_info_id,
                    'modified'           => Carbon::now(),
                ];

                $sort ++;

                if(!empty($sale_slip_detail)) {

                    DB::table('sale_slip_details')->insert($sale_slip_detail);
                    $saleSlipDetailIds[] = DB::getPdo()->lastInsertId();
                }
            }

            // inventory_managesも物理削除して新規登録する

            // -----------------------------
            // 登録されている対象売上データの削除
            // -----------------------------
            \App\InventoryManage::where('sale_detail_slip_id', $SaleSlipDetailData[0]['id'])->delete();

            // -------
            // 新規登録
            // -------
            // 登録データ用格納配列初期化
            $inventoryManage = array();

            if (isset($request->data['InventoryManage']) && !empty($request->data['InventoryManage'])) {
                foreach ($request->data['InventoryManage'] as $key => $requestInventoryManageDatas) {
                    foreach ($requestInventoryManageDatas['supply_slip_id'] as $supplySlipIdKey => $supplySlipId) {

                        // 利用仕入数取得
                        $unitNum = floatval($requestInventoryManageDatas['use_num'][$supplySlipIdKey]);

                        // 登録データ格納
                        $inventoryManage[] = [
                            "sale_detail_slip_id"       => $saleSlipDetailIds[$key],
                            "supply_detail_slip_id"     => $supplySlipId,
                            "unit_num"                  => $unitNum,
                            "sort"                      => $supplySlipIdKey,
                            "created_user_id"           => $user_info_id,
                            "created"                   => Carbon::now(),
                            "modified_user_id"          => $user_info_id,
                            "modified"                  => Carbon::now()
                        ];

                        // 対象の仕入伝票を取得
                        $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                        ->select(
                            'SupplySlipDetail.id           AS supply_slip_detail_id',
                            'SupplySlipDetail.unit_num     AS unit_num',
                            'SupplySlipDetail.consumption  AS consumption'
                        )
                            ->where([
                                ['SupplySlipDetail.id', '=', $supplySlipId],
                                ['SupplySlipDetail.active', '=', 1],
                            ])->first();

                        // もし伝票が存在しない場合はエラーを飛ばす
                        if (empty($SupplySlipDetailList)) {
                            throw new \Exception("存在しない仕入伝票が選択されています。");
                        }

                        $sale_slip_detail_unit_num    = floatval($SupplySlipDetailList->unit_num);
                        $sale_slip_detail_consumption = floatval($SupplySlipDetailList->consumption);
                        $sale_slip_detail_remain      = $sale_slip_detail_unit_num - $sale_slip_detail_consumption;

                        if ($sale_slip_detail_remain < $unitNum) {
                            throw new \Exception("在庫数より多くの仕入数が入ってきています。");
                        }

                        // 今回の利用数を含めて登録
                        $sale_slip_detail_unit_num_new = $sale_slip_detail_consumption + $unitNum;

                        // 仕入伝票登録を登録する
                        $SupplySlipDetail = \App\SupplySlipDetail::find($SupplySlipDetailList->supply_slip_detail_id);
                        $SupplySlipDetail->consumption        = $sale_slip_detail_unit_num_new;
                        $SupplySlipDetail->modified_user_id   = $user_info_id;
                        $SupplySlipDetail->modified           = Carbon::now();
                        $SupplySlipDetail->save();

                        if (!empty($inventoryManage)) {

                            DB::table('inventory_manages')->insert($inventoryManage);
                        }
                    }
                }

        }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./SaleSlipIndex');
    }

    /**
     * 売上登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SaleSlip.create');
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
        $ajaxHtml .= "<select class='form-control' id='standard_id_{$slip_num}' name='data[SaleSlip][standard_id][{$slip_num}]'>";
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
     * 売上企業ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSaleCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $saleCompanyList = DB::table('sale_companies AS SaleCompany')
            ->select(
                'SaleCompany.name  AS sale_company_name'
            )->where([
                    ['SaleCompany.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SaleCompany.name', 'like', "%{$input_text}%")
                ->orWhere('SaleCompany.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($saleCompanyList)) {

                foreach ($saleCompanyList as $sale_company_val) {

                    array_push($auto_complete_array, $sale_company_val->sale_company_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 売上先企業更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSaleCompany(Request $request)
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
            $saleCompanyList = DB::table('sale_companies AS SaleCompany')
            ->select(
                'SaleCompany.code  AS code',
                'SaleCompany.id    AS id',
                'SaleCompany.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SaleCompany.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SaleCompany.name', 'like', $input_name);
            })
            ->first();

            if (!empty($saleCompanyList)) {
                $output_code = $saleCompanyList->code;
                $output_id   = $saleCompanyList->id;
                $output_name = $saleCompanyList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 売上店舗ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSaleShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $saleShopList = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.name  AS sale_shop_name'
            )->where([
                    ['SaleShop.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SaleShop.name', 'like', "%{$input_text}%")
                ->orWhere('SaleShop.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($saleShopList)) {

                foreach ($saleShopList as $sale_shop_val) {

                    array_push($auto_complete_array, $sale_shop_val->sale_shop_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 売上先店舗更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSaleShop(Request $request)
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
            $saleShopList = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.code  AS code',
                'SaleShop.id    AS id',
                'SaleShop.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SaleShop.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SaleShop.name', 'like', $input_name);
            })
            ->first();

            if (!empty($saleShopList)) {
                $output_code = $saleShopList->code;
                $output_id   = $saleShopList->id;
                $output_name = $saleShopList->name;
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
                'Product.code  AS code',
                'Product.id    AS id',
                'Product.name  AS product_name',
                'Tax.id        AS tax_id',
                'Tax.name      AS tax_name',
                'Unit.id       AS unit_id',
                'Unit.name     AS unit_name',
                'InventoryUnit.id   AS inventory_unit_id',
                'InventoryUnit.name AS inventory_unit_name'
            )->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            }
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            }
            )->leftJoin('units AS InventoryUnit', function ($join) {
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
     * 売上伝票の登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerSaleSlips(Request $request)
    {

        $error_message = "";

        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SaleSlipData = $request->data['SaleSlip'];
            $SaleSlipDetailData = $request->data['SaleSlipDetail'];

            //-----------------------
            // SaleSlipモデル 新規作成
            //-----------------------
            $SaleSlipData['user_id'] = $user_info_id;
            $SaleSlip = $this->SaleSlip->insertSaleSlip($SaleSlipData);

            $sort = 0;

            foreach ($SaleSlipDetailData as $SaleSlipDetailKey => $SaleSlipDetailVal) {

                //-----------------------
                // SaleSlipDetailモデル 新規作成
                //-----------------------
                $SaleSlipDetailVal['sale_slip_id'] = $SaleSlip->id;
                $SaleSlipDetailVal['user_id']      = $user_info_id;
                $SaleSlipDetailVal['sort']         = $sort;
                //$SaleSlipDetail = $this->SaleSlipDetail->insertSaleSlipDetail($SaleSlipDetailVal);

                // 値がNULLのところを初期化
                if (empty($SaleSlipDetailVal['standard_id'])) $SaleSlipDetailVal['standard_id'] = 0;
                if (empty($SaleSlipDetailVal['quality_id'])) $SaleSlipDetailVal['quality_id'] = 0;
                if (isset($SaleSlipDetailVal['supply_count']) || empty($SaleSlipDetailVal['supply_count'])) $SaleSlipDetailVal['supply_count'] = 0;
                if (isset($SaleSlipDetailVal['supply_unit_num']) || empty($SaleSlipDetailVal['supply_unit_num'])) $SaleSlipDetailVal['supply_unit_num'] = 0;
                if (isset($SaleSlipDetailVal['sort']) || empty($SaleSlipDetailVal['sort'])) $SaleSlipDetailVal['sort'] = 0;

                // sale_slip_detailsを登録する
                $SaleSlipDetail                     = new SaleSlipDetail;
                $SaleSlipDetail->sale_slip_id       = $SaleSlipDetailVal['sale_slip_id'];
                $SaleSlipDetail->product_id         = $SaleSlipDetailVal['product_id'];
                $SaleSlipDetail->standard_id        = $SaleSlipDetailVal['standard_id'];
                $SaleSlipDetail->quality_id         = $SaleSlipDetailVal['quality_id'];
                $SaleSlipDetail->unit_price         = $SaleSlipDetailVal['unit_price'];
                $SaleSlipDetail->unit_num           = $SaleSlipDetailVal['unit_num'];
                $SaleSlipDetail->notax_price        = $SaleSlipDetailVal['notax_price'];
                $SaleSlipDetail->unit_id            = $SaleSlipDetailVal['unit_id'];
                $SaleSlipDetail->inventory_unit_id  = $SaleSlipDetailVal['inventory_unit_id'];
                $SaleSlipDetail->inventory_unit_num = $SaleSlipDetailVal['inventory_unit_num'];
                $SaleSlipDetail->staff_id           = $SaleSlipDetailVal['staff_id'];
                $SaleSlipDetail->memo               = $SaleSlipDetailVal['memo'];
                $SaleSlipDetail->supply_count       = $SaleSlipDetailVal['supply_count'];
                $SaleSlipDetail->supply_unit_num    = $SaleSlipDetailVal['supply_unit_num'];
                $SaleSlipDetail->sort               = $sort;
                $SaleSlipDetail->created_user_id    = $SaleSlipDetailVal['user_id'];
                $SaleSlipDetail->created            = Carbon::now();
                $SaleSlipDetail->created_user_id    = $SaleSlipDetailVal['user_id'];
                $SaleSlipDetail->modified           = Carbon::now();
                $SaleSlipDetail->save();

                $sort++;

                // 作成したIDを取得する
                $sale_slip_detail_new_id = $SaleSlipDetail->id;

                // 在庫管理配列を取得
                if (isset($request->data['InventoryManage'][$SaleSlipDetailKey])) {

                    // 在庫管理配列を取得
                    $inventoryManageData = $request->data['InventoryManage'][$SaleSlipDetailKey];

                    // inventory_managesを登録する
                    $inventoryManage = array();

                    foreach ($inventoryManageData['supply_slip_id'] as $supply_slip_key => $supply_slip_val) {

                        // 利用仕入数取得
                        $unit_num = floatval($inventoryManageData['use_num'][$supply_slip_key]);

                        // 在庫管理テーブルの配列作成
                        $inventoryManage[] = [
                            "sale_detail_slip_id"       => $sale_slip_detail_new_id,
                            "supply_detail_slip_id"     => $supply_slip_val,
                            "unit_num"                  => $unit_num,
                            "sort"                      => $supply_slip_key,
                            "created_user_id"           => $user_info_id,
                            "created"                   => Carbon::now(),
                            "modified_user_id"          => $user_info_id,
                            "modified"                  => Carbon::now()
                        ];

                        // 対象の仕入伝票を取得
                        $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                        ->select(
                            'SupplySlipDetail.id           AS supply_slip_detail_id',
                            'SupplySlipDetail.unit_num     AS unit_num',
                            'SupplySlipDetail.consumption  AS consumption'
                        )
                        ->where([
                                ['SupplySlipDetail.id', '=', $supply_slip_val],
                                ['SupplySlipDetail.active', '=', 1],
                        ])->first();

                        // もし伝票が存在しない場合はエラーを飛ばす
                        if (empty($SupplySlipDetailList)) {
                            throw new \Exception("存在しない仕入伝票が選択されています。");
                        }

                        $sale_slip_detail_unit_num    = floatval($SupplySlipDetailList->unit_num);
                        $sale_slip_detail_consumption = floatval($SupplySlipDetailList->consumption);
                        $sale_slip_detail_remain      = $sale_slip_detail_unit_num - $sale_slip_detail_consumption;

                        if ($sale_slip_detail_remain < $unit_num) {
                            throw new \Exception("在庫数より多くの仕入数が入ってきています。");
                        }

                        // 今回の利用数を含めて登録
                        $sale_slip_detail_unit_num_new = $sale_slip_detail_consumption + $unit_num;

                        // 仕入伝票登録を登録する
                        $SupplySlipDetail = \App\SupplySlipDetail::find($SupplySlipDetailList->supply_slip_detail_id);
                        $SupplySlipDetail->consumption        = $sale_slip_detail_unit_num_new;
                        $SupplySlipDetail->modified_user_id   = $user_info_id;
                        $SupplySlipDetail->modified           = Carbon::now();
                        $SupplySlipDetail->save();
                    }

                    if (!empty($inventoryManage)) {

                        DB::table('inventory_manages')->insert($inventoryManage);
                    }
                }
            }

            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();
            dd($e);
        }

        return redirect('./SaleSlipIndex');
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

        $tabInitialNum = intval(7*$slip_num + 3);

        // 追加伝票形成
        $ajaxHtml1 = '';
        $ajaxHtml1 .= " <tr id='slip-partition-".$slip_num."' class='partition-area'>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-upper-".$slip_num."'>";
        $ajaxHtml1 .= "     <td class='width-10' id='product-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='product_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][product_id]'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-20'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='product_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][product_text]' placeholder='製品欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td class='width-15' colspan='2'>";
        //$ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_price_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_price]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 1)."'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='inventory_unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_num]' tabindex='".($tabInitialNum + 1)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_text]' placeholder='個数欄' readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='inventory_unit_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_id]' value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-10' id='staff-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='staff_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][staff_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-20'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='staff_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][staff_text]' placeholder='担当欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-15'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='tax_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][tax_text]'' placeholder='税率欄'  readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='tax_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][tax_id]'  value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td rowspan='4' class='width-5'>";
        $ajaxHtml1 .= "         <button id='remove-slip-btn' type='button' class='btn remove-slip-btn btn-secondary' onclick='javascript:removeSlip(".$slip_num.") '>削除</button>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-middle-".$slip_num."'>";
        $ajaxHtml1 .= "     <td id='standard-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='standard_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][standard_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='standard_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][standard_text]' placeholder='規格欄'  readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_num]' tabindex='".($tabInitialNum + 2)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='inventory_unit_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_text]' placeholder='個数欄' readonly>";
        //$ajaxHtml1 .= "         <input type='hidden' id='inventory_unit_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][inventory_unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_num]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 2)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_text]' placeholder='数量欄' readonly>";
        $ajaxHtml1 .= "         <input type='hidden' id='unit_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_id]' value='0'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='button' class='form-control btn btn-primary' id='supply_sale_slip_btn_".$slip_num."' value='対応仕入' onclick='javascript:showSupplySaleModal(".$slip_num.")'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='sale_supply_slip_count_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][supply_count]' value='0' readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='sale_supply_slip_unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][supply_unit_num]' value='0' readonly>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-lower-".$slip_num."'>";
        $ajaxHtml1 .= "     <td id='quality-code-area-".$slip_num."'>";
        $ajaxHtml1 .= "         <input type='hidden' id='quality_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][quality_id]' >";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='quality_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][quality_text]' placeholder='品質欄' readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_num]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 3)."'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='unit_text_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_text]' placeholder='数量欄' readonly>";
        //$ajaxHtml1 .= "         <input type='hidden' id='unit_id_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_id]' value='0'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td colspan='2'>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='notax_price_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][notax_price]'  value='0' readonly>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='button' class='form-control btn btn-primary' id='supply_sale_slip_btn_".$slip_num."' value='対応仕入' onclick='javascript:showSupplySaleModal(".$slip_num.")'>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='sale_supply_slip_count_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][supply_count]' value='0' readonly>";
        //$ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='sale_supply_slip_unit_num_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][supply_unit_num]' value='0' readonly>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td class='width-15' colspan='2'>";
        $ajaxHtml1 .= "         <input type='number' class='form-control' id='unit_price_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][unit_price]' onKeyUp='javascript:priceNumChange(".$slip_num.")' tabindex='".($tabInitialNum + 3)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= "     <td colspan='3'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='memo_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][memo]' placeholder='摘要欄' tabindex='".($tabInitialNum + 5)."'>";
        $ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";
        $ajaxHtml1 .= " <tr id='slip-most-lower-".$slip_num."'>";
        $ajaxHtml1 .= "     <td>小計</td>";
        $ajaxHtml1 .= "     <td colspan='3'>";
        $ajaxHtml1 .= "         <input type='text' class='form-control' id='notax_price_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][notax_price]' value='0' readonly>";
        $ajaxHtml1 .= "     </td>";
        //$ajaxHtml1 .= "     <td colspan='7'>";
        //$ajaxHtml1 .= "         <input type='text' class='form-control' id='memo_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][memo]' tabindex='".($tabInitialNum + 5)."'>";
        //$ajaxHtml1 .= "     </td>";
        $ajaxHtml1 .= " </tr>";

        // 仕入伝票リスト格納エリア
        $ajaxHtml2 = " <div id='supply-slip-area-".$slip_num."'></div>";

        //-------------------------------
        // AutoCompleteの要素は別で形成する
        //-------------------------------
        // 製品ID
        $autoCompleteProduct = "<input type='text' class='form-control product_code_input' id='product_code_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][product_code]' tabindex='".$tabInitialNum."'>";
        // 規格ID
        $autoCompleteStandard = "<input type='text' class='form-control standard_code_input' id='standard_code_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][standard_code]'>";
        // 品質ID
        $autoCompleteQuality = "<input type='text' class='form-control quality_code_input' id='quality_code_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][quality_code]'>";
        // 担当
        $autoCompleteStaff = "<input type='text' class='form-control staff_code_input' id='staff_code_".$slip_num."' name='data[SaleSlipDetail][".$slip_num."][staff_code]' tabindex='".($tabInitialNum + 4)."'>";

        $slip_num = intval($slip_num) + 1;

        $returnArray = array($slip_num, $ajaxHtml1, $ajaxHtml2, $autoCompleteProduct, $autoCompleteStandard, $autoCompleteQuality, $autoCompleteStaff);


        return $returnArray;
    }

    /**
     * 売上対象製品取得
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxShowSupplySlip(Request $request)
    {

        // 初期化
        $ajaxHtml = "";

        // 製品IDを取得
        $product_id = $request->product_id;
        if(empty($product_id)) {

            $returnArray = array($ajaxHtml);
            return $returnArray;
        }

        // use_num_arr取得
        if (!empty($request->use_num_arr)) {
            $use_num_arr = explode(",", $request->use_num_arr);
        } else {
            $use_num_arr = null;
        }

        // supply_slip_id_arr取得
        if (!empty($request->supply_slip_id_arr)) {
            $supply_slip_id_arr = explode(",", $request->supply_slip_id_arr);
        } else {
            $supply_slip_id_arr = null;
        }

        // sale_slip_id取得
        if (isset($request->sale_slip_id) && !empty($request->sale_slip_id)) {
            $sale_slip_id = $request->sale_slip_id;
        } else {
            $sale_slip_id = null;
        }

        try {
            // 新規作成か編集かでwhere句の条件を分岐させる
            $action = $request->action;
            if ($action == 'create') {
                $where = 'CAST(SupplySlipDetail.unit_num AS SIGNED) > CAST(SupplySlipDetail.consumption AS SIGNED)';
            } else {
                $where = 'CAST(SupplySlipDetail.unit_num AS SIGNED) > (CAST(SupplySlipDetail.consumption AS SIGNED) - CAST(InventoryManage.unit_num AS SIGNED))';
            }

            // inventory_managesのサブクエリを作成
            $inventory_sub_query = null;
            if ($action == 'edit') {
                $inventory_sub_query = DB::table('inventory_manages as SubInventoryManage')
                ->select(
                    'SubInventoryManage.supply_detail_slip_id AS supply_detail_slip_id'
                )
                ->selectRaw('sum(SubInventoryManage.unit_num) AS unit_num')
                ->leftJoin('supply_slip_details as SubSupplySlipDetail', function ($join) {
                    $join->on('SubSupplySlipDetail.id', '=', 'SubInventoryManage.supply_detail_slip_id');
                })
                ->groupBy('supply_detail_slip_id');
            }

            // 製品名取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.name           AS product_name',
                'Unit.name              AS unit_name'
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })->where([
                ['Product.id', '=', $product_id],
                ['Product.active', '=', 1],
            ])->first();

            // 売上対象の製品をDBから取得
            $supplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlipDetail.id           AS supply_slip_id',
                'SupplyCompany.name            AS supply_company_name',
                'SupplyShop.name               AS supply_shop_name',
                'Product.name                  AS product_name',
                'Standard.name                 AS standard_name',
                'Quality.name                  AS quality_name',
                'SupplySlipDetail.unit_price   AS unit_price',
                'SupplySlipDetail.unit_num     AS unit_num',
                'Unit.name                     AS unit_name',
                'SupplySlipDetail.notax_price  AS notax_price',
                'OriginArea.name               AS originArea_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%m/%d") AS supply_slip_supply_date')
            ->selectRaw('(CAST(SupplySlipDetail.unit_num AS SIGNED) - CAST(SupplySlipDetail.consumption AS SIGNED)) AS remain_num')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('products AS Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftJoin('standards AS Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id');
            })
            ->leftJoin('qualities AS Quality', function ($join) {
                $join->on('Quality.id', '=', 'SupplySlipDetail.quality_id');
            })
            ->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->leftJoin('origin_areas AS OriginArea', function ($join) {
                $join->on('OriginArea.id', '=', 'SupplySlipDetail.origin_area_id');
            })
            ->join('staffs AS Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id');
            })
            ->join('supply_slips AS SupplySlip', function ($join) {
                $join->on('SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id');
            })
            ->when($action == 'edit', function($query) use ($inventory_sub_query) {
                $query
                ->leftJoin(DB::raw('('. $inventory_sub_query->toSql() .') as InventoryManage'), 'InventoryManage.supply_detail_slip_id', '=', 'SupplySlipDetail.id');
            })
            ->where([
                ['SupplySlipDetail.product_id', '=', $product_id],
                ['SupplySlipDetail.unit_num', '>', 0],
                ['SupplySlipDetail.active', '=', 1],
            ])
            ->whereRaw($where)
            ->orderBy('SupplySlip.date', 'asc')->get();

            // HTMLを形成
            $ajaxHtml .= "<div class='product-area' id='modal_product_area'>".$productList->product_name."</div>";
            $ajaxHtml .= "<table class='modal-table' id='modal_table_area'>";
            $ajaxHtml .= "  <tr>";
            $ajaxHtml .= "    <th>選択</th>";
            $ajaxHtml .= "    <th>利用数</th>";
            $ajaxHtml .= "    <th>仕入日</th>";
            $ajaxHtml .= "    <th>企業名</th>";
            $ajaxHtml .= "    <th>店舗名</th>";
            $ajaxHtml .= "    <th>規格</th>";
            $ajaxHtml .= "    <th>品質</th>";
            $ajaxHtml .= "    <th>単価</th>";
            $ajaxHtml .= "    <th>在庫数</th>";
            $ajaxHtml .= "    <th>産地</th>";
            $ajaxHtml .= "    <th>スタッフ</th>";
            $ajaxHtml .= "  </tr>";

            // 初期化処理
            $use_num_count = 0; // 件数
            $use_num_total = 0; // 単位合計

            if (!empty($supplySlipDetailList)) {

                foreach ($supplySlipDetailList as $supplySlipDetails) {

                    $detail_id = $supplySlipDetails->supply_slip_id;
                    $checked   = "";
                    $remain_num = $supplySlipDetails->remain_num;

                    if (!empty($supply_slip_id_arr)) {

                        foreach ($supply_slip_id_arr as $key => $supply_slip_id_val) {

                            if ($detail_id == $supply_slip_id_val) {
                                $checked   = "checked";
                                $remain_num = $use_num_arr[$key];
                                $use_num_count += 1;
                                $use_num_total += intval($remain_num);
                            }
                        }
                    }

                    $ajaxHtml .= "  <tr>";
                    $ajaxHtml .= "    <td><input type='checkbox' class='modal-checkbox' id='checkbox_".$detail_id."' $checked></td>";
                    $ajaxHtml .= "    <td><input type='tel' class='modal-sale-num' id='use_num_".$detail_id."' value='".$remain_num."'></td>";
                    $ajaxHtml .= "    <td><div id='date_".$detail_id."'>".$supplySlipDetails->supply_slip_supply_date."</div></td>";
                    $ajaxHtml .= "    <td><div id='company_".$detail_id."'>".$supplySlipDetails->supply_company_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='shop_".$detail_id."'>".$supplySlipDetails->supply_shop_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='standard_".$detail_id."'>".$supplySlipDetails->standard_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='quality_".$detail_id."'>".$supplySlipDetails->quality_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='unit_price_".$detail_id."'>".$supplySlipDetails->unit_price."</div></td>";
                    $ajaxHtml .= "    <td>";
                    $ajaxHtml .= "        <div id='remain_num_unit_".$detail_id."'>".$supplySlipDetails->remain_num.$supplySlipDetails->unit_name."</div>";
                    $ajaxHtml .= "        <input type='hidden' id='remain_num_".$detail_id."' value='".$supplySlipDetails->remain_num."'>";
                    $ajaxHtml .= "    </td>";
                    $ajaxHtml .= "    <td><div id='origin_area_".$detail_id."'>".$supplySlipDetails->originArea_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='staff_".$detail_id."'>".$supplySlipDetails->staff_name."</div></td>";
                    $ajaxHtml .= "  </tr>";
                }
            }

            $ajaxHtml .= "</table>";
            $ajaxHtml .= "<div id='sum-area'>";
            $ajaxHtml .= "    <div id='sum_count_area'>".$use_num_count."</div>";
            $ajaxHtml .= "    <div id='sum_count_name_area'>件</div>";
            $ajaxHtml .= "    <div id='sum_unit_num_area'>".$use_num_total."</div>";
            $ajaxHtml .= "    <div id='sum_unit_name_area'>".$productList->unit_name."</div>";
            $ajaxHtml .= "    <input type='hidden' id='sale_slip_no' value=''>";
            $ajaxHtml .= "<div>";

            $returnArray = array($ajaxHtml);

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return $returnArray;
    }
}

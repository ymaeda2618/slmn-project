<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class DailyPerformanceController extends Controller
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
     * 日別仕入売上一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $dp_date_type     = $request->session()->get('dp_date_type');
            $dp_daily_performance_target_year     = $request->session()->get('dp_daily_performance_target_year');
            $dp_daily_performance_target_month    = $request->session()->get('dp_daily_performance_target_month');

            // 空値の場合は初期値を設定
            if(empty($dp_date_type)) $dp_date_type = 1;
            if(empty($dp_daily_performance_target_year))  $dp_daily_performance_target_year    = date('Y');
            if(empty($dp_daily_performance_target_month)) $dp_daily_performance_target_month  = date('m');

            $dp_supply_company_code  = $request->session()->get('dp_supply_company_code');
            $dp_supply_company_id    = $request->session()->get('dp_supply_company_id');
            $dp_supply_company_text  = $request->session()->get('dp_supply_company_text');
            $dp_supply_shop_code     = $request->session()->get('dp_supply_shop_code');
            $dp_supply_shop_id       = $request->session()->get('dp_supply_shop_id');
            $dp_supply_shop_text     = $request->session()->get('dp_supply_shop_text');

            $dp_sale_company_code  = $request->session()->get('dp_sale_company_code');
            $dp_sale_company_id    = $request->session()->get('dp_sale_company_id');
            $dp_sale_company_text  = $request->session()->get('dp_sale_company_text');
            $dp_sale_shop_code     = $request->session()->get('dp_sale_shop_code');
            $dp_sale_shop_id       = $request->session()->get('dp_sale_shop_id');
            $dp_sale_shop_text     = $request->session()->get('dp_sale_shop_text');


            $dp_product_code  = $request->session()->get('dp_product_code');
            $dp_product_id    = $request->session()->get('dp_product_id');
            $dp_product_text  = $request->session()->get('dp_product_text');


            $dp_staff_code  = $request->session()->get('dp_staff_code');
            $dp_staff_id    = $request->session()->get('dp_staff_id');
            $dp_staff_text  = $request->session()->get('dp_staff_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $dp_date_type     = $request->data['DailyPerformance']['date_type'];

                $dp_supply_company_code  = $request->data['DailyPerformance']['supply_company_code'];
                $dp_supply_company_id    = $request->data['DailyPerformance']['supply_company_id'];
                $dp_supply_company_text  = $request->data['DailyPerformance']['supply_company_text'];
                $dp_supply_shop_code     = $request->data['DailyPerformance']['supply_shop_code'];
                $dp_supply_shop_id       = $request->data['DailyPerformance']['supply_shop_id'];
                $dp_supply_shop_text     = $request->data['DailyPerformance']['supply_shop_text'];

                $dp_sale_company_code  = $request->data['DailyPerformance']['sale_company_code'];
                $dp_sale_company_id    = $request->data['DailyPerformance']['sale_company_id'];
                $dp_sale_company_text  = $request->data['DailyPerformance']['sale_company_text'];
                $dp_sale_shop_code     = $request->data['DailyPerformance']['sale_shop_code'];
                $dp_sale_shop_id       = $request->data['DailyPerformance']['sale_shop_id'];
                $dp_sale_shop_text     = $request->data['DailyPerformance']['sale_shop_text'];

                $dp_product_code       = $request->data['DailyPerformance']['product_code'];
                $dp_product_id         = $request->data['DailyPerformance']['product_id'];
                $dp_product_text       = $request->data['DailyPerformance']['product_text'];

                $dp_staff_code         = $request->data['DailyPerformance']['staff_code'];
                $dp_staff_id           = $request->data['DailyPerformance']['staff_id'];
                $dp_staff_text         = $request->data['DailyPerformance']['staff_text'];

                // 日付の設定
                $dp_daily_performance_target_year     = $request->data['DailyPerformance']['target_year'];
                $dp_daily_performance_target_month    = $request->data['DailyPerformance']['target_month'];

                $request->session()->put('dp_date_type', $dp_date_type);
                $request->session()->put('dp_daily_performance_target_year', $dp_daily_performance_target_year);
                $request->session()->put('dp_daily_performance_target_month', $dp_daily_performance_target_month);

                $request->session()->put('dp_supply_company_code', $dp_supply_company_code);
                $request->session()->put('dp_supply_company_id', $dp_supply_company_id);
                $request->session()->put('dp_supply_company_text', $dp_supply_company_text);
                $request->session()->put('dp_supply_shop_code', $dp_supply_shop_code);
                $request->session()->put('dp_supply_shop_id', $dp_supply_shop_id);
                $request->session()->put('dp_supply_shop_text', $dp_supply_shop_text);

                $request->session()->put('dp_sale_company_code', $dp_sale_company_code);
                $request->session()->put('dp_sale_company_id', $dp_sale_company_id);
                $request->session()->put('dp_sale_company_text', $dp_sale_company_text);
                $request->session()->put('dp_sale_shop_code', $dp_sale_shop_code);
                $request->session()->put('dp_sale_shop_id', $dp_sale_shop_id);
                $request->session()->put('dp_sale_shop_text', $dp_sale_shop_text);

                $request->session()->put('dp_product_code', $dp_product_code);
                $request->session()->put('dp_product_id', $dp_product_id);
                $request->session()->put('dp_product_text', $dp_product_text);

                $request->session()->put('dp_staff_code', $dp_staff_code);
                $request->session()->put('dp_staff_id', $dp_staff_id);
                $request->session()->put('dp_staff_text', $dp_staff_text);

            } else { // リセットボタンが押された時の処理

                $dp_date_type     = 1;
                $dp_daily_performance_target_year     = date('Y');
                $dp_daily_performance_target_month    = date('m');

                $dp_supply_company_code  = null;
                $dp_supply_company_id    = null;
                $dp_supply_company_text  = null;
                $dp_supply_shop_code     = null;
                $dp_supply_shop_id       = null;
                $dp_supply_shop_text     = null;

                $dp_sale_company_code  = null;
                $dp_sale_company_id    = null;
                $dp_sale_company_text  = null;
                $dp_sale_shop_code     = null;
                $dp_sale_shop_id       = null;
                $dp_sale_shop_text     = null;

                $dp_product_code       = null;
                $dp_product_id         = null;
                $dp_product_text       = null;

                $dp_staff_code         = null;
                $dp_staff_id           = null;
                $dp_staff_text         = null;

                $request->session()->forget('dp_date_type');
                $request->session()->forget('dp_daily_performance_target_year');
                $request->session()->forget('dp_daily_performance_target_month');

                $request->session()->forget('dp_supply_company_code');
                $request->session()->forget('dp_supply_company_id');
                $request->session()->forget('dp_supply_company_text');
                $request->session()->forget('dp_supply_shop_code');
                $request->session()->forget('dp_supply_shop_id');
                $request->session()->forget('dp_supply_shop_text');

                $request->session()->forget('dp_sale_company_code');
                $request->session()->forget('dp_sale_company_id');
                $request->session()->forget('dp_sale_company_text');
                $request->session()->forget('dp_sale_shop_code');
                $request->session()->forget('dp_sale_shop_id');
                $request->session()->forget('dp_sale_shop_text');

                $request->session()->forget('dp_product_code');
                $request->session()->forget('dp_product_id');
                $request->session()->forget('dp_product_text');

                $request->session()->forget('dp_staff_code');
                $request->session()->forget('dp_staff_id');
                $request->session()->forget('dp_staff_text');
            }
        }

        try {

            // 対象日付のチェック
            $dp_check_str_slip_date = "";
            $dp_check_str_deliver_date = "";
            if($dp_date_type == 1) $dp_check_str_slip_date = "checked";
            else  $dp_check_str_deliver_date = "checked";

            // 検索項目で利用する対象年の配列を作成
            $year_arr   = array();
            $from_year  = date('Y', strtotime('-2 year'));
            $to_year    = date('Y', strtotime('+2 year'));

            for($y = $from_year; $y <= $to_year; $y++){
                $year_arr[$y] = $y . '年';
            }

            // 検索項目で利用する対象月の配列を作成
            $month_arr   = array();

            for($m = 1; $m <= 12; $m++){
                $month_arr[$m] = $m . '月';
            }

            // 日付配列を作成
            $date_arr = array();
            $target_year_month = $dp_daily_performance_target_year . '-' . $dp_daily_performance_target_month;
            $first_date = date('Y-m-d', strtotime('first day of ' . $target_year_month));
            $last_date  = date('Y-m-d', strtotime('last day of ' . $target_year_month));
            $diff       = (strtotime($last_date) - strtotime($first_date)) / ( 60 * 60 * 24);

            for($d = 0; $d <= $diff; $d++){
                $date_arr[] = date('Y-m-d', strtotime($first_date . '+' . $d . 'days'));
            }

            //---------------------
            // 仕入額を取得
            //---------------------

            // supply_slip_detailsのサブクエリを作成
            $dp_supply_sub_query = null;
            if(!empty($dp_product_id) || !empty($dp_staff_id)) {

                $dp_supply_sub_query = DB::table('supply_slip_details as SubTable')
                ->select('SubTable.supply_slip_id AS supply_slip_id')
                ->selectRaw(
                    'CASE
                       WHEN SubProduct.tax_id = 1 THEN SUM(COALESCE(SubTable.notax_price,0))*1.08
                       WHEN SubProduct.tax_id = 2 THEN SUM(COALESCE(SubTable.notax_price,0))*1.10
                     END AS sub_supply_detail_daily_amount'
                    )
                ->join('products AS SubProduct', function ($join) {
                    $join->on('SubProduct.id', '=', 'SubTable.product_id');
                })
                ->if(!empty($dp_product_id), function ($dp_supply_sub_query) use ($dp_product_id) {
                    return $dp_supply_sub_query->where('SubTable.product_id', '=', $dp_product_id);
                })
                ->if(!empty($dp_staff_id), function ($dp_supply_sub_query) use ($dp_staff_id) {
                    return $dp_supply_sub_query->where('SubTable.staff_id', '=', $dp_staff_id);
                })
                ->groupBy('SubTable.supply_slip_id');
            }

            $supplySlipList = DB::table('supply_slips AS SupplySlip')

            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y-%m-%d")          AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y-%m-%d") AS supply_slip_delivery_date')
            ->selectRaw('SUM(COALESCE(SupplySlip.total,0))              AS supply_daily_amount')
            ->if(!empty($dp_product_id), function ($query) {
                return $query->selectRaw('SUM(COALESCE(SupplySlipDetail.sub_supply_detail_daily_amount,0)) AS supply_detail_daily_amount');
            })

            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id');
            })
            ->if(!empty($dp_supply_sub_query), function ($query) use ($dp_supply_sub_query) {
                return $query
                       ->join(DB::raw('('. $dp_supply_sub_query->toSql() .') as SupplySlipDetail'), 'SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id')
                       ->mergeBindings($dp_supply_sub_query);
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 1, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SupplySlip.date', [$first_date, $last_date]);
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 2, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SupplySlip.delivery_date', [$first_date, $last_date]);
            })
            ->if(!empty($dp_supply_company_id), function ($query) use ($dp_supply_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $dp_supply_company_id);
            })
            ->if(!empty($dp_supply_shop_id), function ($query) use ($dp_supply_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $dp_supply_shop_id);
            })
            ->where('SupplySlip.active', '=', '1')
            ->if($dp_date_type == 1, function ($query) {
                return $query->groupBy('SupplySlip.date');
            })
            ->if($dp_date_type == 2, function ($query) {
                return $query->groupBy('SupplySlip.delivery_date');
            })->get();

            // 配列を組みなおす
            $supply_date_arr = array();

            if (!empty($supplySlipList)) {
                foreach ($supplySlipList as $supplySlipVal) {

                    if ($dp_date_type == 1) {
                        $supply_date = $supplySlipVal->supply_slip_date;
                    } else {
                        $supply_date = $supplySlipVal->supply_slip_delivery_date;
                    }

                    if(!empty($dp_product_id)){
                        $supply_daily_amount  = $supplySlipVal->supply_detail_daily_amount;
                    } else {
                        $supply_daily_amount  = $supplySlipVal->supply_daily_amount;
                    }

                    $supply_date_arr[$supply_date] = [
                        "supply_daily_amount"  => $supply_daily_amount
                    ];
                }
            }

            //---------------------
            // 売上額を取得
            //---------------------
            // supply_slip_detailsのサブクエリを作成
            $dp_sale_sub_query = null;
            if(!empty($dp_product_id) || !empty($dp_staff_id)) {

                $dp_sale_sub_query = DB::table('sale_slip_details as SaleSubTable')
                ->select('SaleSubTable.sale_slip_id AS sale_slip_id')
                ->selectRaw(
                    'CASE
                       WHEN SubProduct.tax_id = 1 THEN SUM(COALESCE(SaleSubTable.notax_price,0))*1.08
                       WHEN SubProduct.tax_id = 2 THEN SUM(COALESCE(SaleSubTable.notax_price,0))*1.10
                     END AS sub_sale_detail_daily_amount'
                    )
                ->join('products AS SubProduct', function ($join) {
                    $join->on('SubProduct.id', '=', 'SaleSubTable.product_id');
                })
                ->if(!empty($dp_product_id), function ($dp_sale_sub_query) use ($dp_product_id) {
                    return $dp_sale_sub_query->where('SaleSubTable.product_id', '=', $dp_product_id);
                })
                ->if(!empty($dp_staff_id), function ($dp_sale_sub_query) use ($dp_staff_id) {
                    return $dp_sale_sub_query->where('SaleSubTable.staff_id', '=', $dp_staff_id);
                })
                ->groupBy('SaleSubTable.sale_slip_id');
            }

            $saleSlipList = DB::table('sale_slips AS SaleSlip')

            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y-%m-%d")          AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y-%m-%d") AS sale_slip_delivery_date')
            ->selectRaw('SUM(COALESCE(SaleSlip.total,0))                 AS sale_daily_amount')
            ->if(!empty($dp_sale_sub_query), function ($query) {
                return $query->selectRaw('SUM(COALESCE(SaleSlipDetail.sub_sale_detail_daily_amount,0)) AS sale_detail_daily_amount');
            })
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })
            ->if(!empty($dp_sale_sub_query), function ($query) use ($dp_sale_sub_query) {
                return $query
                       ->join(DB::raw('('. $dp_sale_sub_query->toSql() .') as SaleSlipDetail'), 'SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                       ->mergeBindings($dp_sale_sub_query);
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 1, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SaleSlip.date', [$first_date, $last_date]);
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 2, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SaleSlip.delivery_date', [$first_date, $last_date]);
            })
            ->if(!empty($dp_sale_company_id), function ($query) use ($dp_sale_company_id) {
                return $query->where('SaleSlip.sale_company_id', '=', $dp_sale_company_id);
            })
            ->if(!empty($dp_sale_shop_id), function ($query) use ($dp_sale_shop_id) {
                return $query->where('SaleSlip.sale_shop_id', '=', $dp_sale_shop_id);
            })
            ->where('SaleSlip.active', '=', '1')
            ->if($dp_date_type == 1, function ($query) {
                return $query->groupBy('SaleSlip.date');
            })
            ->if($dp_date_type == 2, function ($query) {
                return $query->groupBy('SaleSlip.delivery_date');
            })->get();

            // 配列を組みなおす
            $sale_date_arr = array();
            if (!empty($saleSlipList)) {
                foreach ($saleSlipList as $saleSlipVal) {

                    if ($dp_date_type == 1) {
                        $sale_date = $saleSlipVal->sale_slip_date;
                    } else {
                        $sale_date = $saleSlipVal->sale_slip_delivery_date;
                    }

                    if(!empty($dp_product_id)){
                        $sale_daily_amount  = $saleSlipVal->sale_detail_daily_amount;
                    } else {
                        $sale_daily_amount  = $saleSlipVal->sale_daily_amount;
                    }

                    $sale_date_arr[$sale_date] = [
                        "sale_daily_amount"  => $sale_daily_amount
                    ];
                }
            }

            //---------------------
            // 日別仕入売上額配列を取得
            //---------------------

            $daily_performance_arr = array();
            $supply_total_amount   = 0;
            $sale_total_amount     = 0;

            foreach ($date_arr as $date_val) {

                $supply_daily_amount = 0;
                $sale_daily_amount   = 0;

                if (isset($supply_date_arr[$date_val])) $supply_daily_amount = $supply_date_arr[$date_val]['supply_daily_amount'];
                if (isset($sale_date_arr[$date_val]))   $sale_daily_amount   = $sale_date_arr[$date_val]['sale_daily_amount'];

                $daily_performance_arr[$date_val] = [
                    "supply_daily_amount"    => $supply_daily_amount,
                    "sale_daily_amount"      => $sale_daily_amount,
                ];

                $supply_total_amount   += $supply_daily_amount;
                $sale_total_amount     += $sale_daily_amount;

            }

        } catch (\Exception $e) {

            dd($e);

            return view('DailyPerformance.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('DailyPerformance.index')->with([
            "year_arr"                          => $year_arr,
            "month_arr"                         => $month_arr,

            "dp_check_str_slip_date"            => $dp_check_str_slip_date,
            "dp_check_str_deliver_date"         => $dp_check_str_deliver_date,
            "dp_daily_performance_target_year"  => $dp_daily_performance_target_year,
            "dp_daily_performance_target_month" => $dp_daily_performance_target_month,

            "dp_supply_company_code"            => $dp_supply_company_code,
            "dp_supply_company_id"              => $dp_supply_company_id,
            "dp_supply_company_text"            => $dp_supply_company_text,
            "dp_supply_shop_code"               => $dp_supply_shop_code,
            "dp_supply_shop_id"                 => $dp_supply_shop_id,
            "dp_supply_shop_text"               => $dp_supply_shop_text,

            "dp_sale_company_code"              => $dp_sale_company_code,
            "dp_sale_company_id"                => $dp_sale_company_id,
            "dp_sale_company_text"              => $dp_sale_company_text,
            "dp_sale_shop_code"                 => $dp_sale_shop_code,
            "dp_sale_shop_id"                   => $dp_sale_shop_id,
            "dp_sale_shop_text"                 => $dp_sale_shop_text,

            "dp_product_code"                   => $dp_product_code,
            "dp_product_id"                     => $dp_product_id,
            "dp_product_text"                   => $dp_product_text,

            "dp_staff_code"                     => $dp_staff_code,
            "dp_staff_id"                       => $dp_staff_id,
            "dp_staff_text"                     => $dp_staff_text,

            "supply_total_amount"               => $supply_total_amount,
            "sale_total_amount"                 => $sale_total_amount,

            "daily_performance_arr"             => $daily_performance_arr,
        ]);
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
            // 先頭2文字がis001などの場合
            $replace_result = substr_replace($input_text, '', 0, 2);
            if(is_numeric($replace_result)){
                $product_code = $input_text;
                $product_name = null;
            }

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
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 担当者DB取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.name  AS staff_name'
            )->where([
                    ['Staff.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Staff.name_sei', 'like', "%{$input_text}%")
                ->orWhere('Staff.name_mei', 'like', "%{$input_text}%")
                ->orWhere('Staff.yomi_sei', 'like', "%{$input_text}%")
                ->orWhere('Staff.yomi_mei', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($staffList)) {

                foreach ($staffList as $staff_val) {

                    array_push($auto_complete_array, $staff_val->staff_name);
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
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $staff_code = $input_text;
            $staff_name = null;
        } else {
            $staff_code = null;
            $staff_name = $input_text;
        }

        // 初期化
        $output_staff_code          = null;
        $output_staff_id            = null;
        $output_staff_name          = null;

        if (!empty($input_text)) {

            // 担当者DB取得
            // 担当者一覧を取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.code       AS code',
                'Staff.id         AS id'
            ) ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name'
            )->if(!empty($staff_code), function ($query) use ($staff_code) {
                return $query->where('Staff.code', '=', $staff_code);
            })
            ->if(!empty($staff_name), function ($query) use ($staff_name) {
                return $query->whereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "%'.$staff_name.'%"');
            })->first();

            if (!empty($staffList)) {
                $output_staff_code        = $staffList->code;
                $output_staff_id          = $staffList->id;
                $output_staff_name        = $staffList->staff_name;
            }
        }

        $returnArray = array(
            $output_staff_code,
            $output_staff_id,
            $output_staff_name
        );

        return json_encode($returnArray);
    }
}

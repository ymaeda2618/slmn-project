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

                $dp_product_code  = $request->data['DailyPerformance']['product_code'];
                $dp_product_id    = $request->data['DailyPerformance']['product_id'];
                $dp_product_text  = $request->data['DailyPerformance']['product_text'];

                // 日付の設定
                $dp_daily_performance_target_year     = $request->data['DailyPerformance']['target_year'];
                $dp_daily_performance_target_month    = $request->data['DailyPerformance']['target_month'];

                $request->session()->put('date_type', $dp_date_type);
                $request->session()->put('daily_performance_target_year', $dp_daily_performance_target_year);
                $request->session()->put('daily_performance_target_month', $dp_daily_performance_target_month);

                $request->session()->put('supply_company_code', $dp_supply_company_code);
                $request->session()->put('supply_company_id', $dp_supply_company_id);
                $request->session()->put('supply_company_text', $dp_supply_company_text);
                $request->session()->put('supply_shop_code', $dp_supply_shop_code);
                $request->session()->put('supply_shop_id', $dp_supply_shop_id);
                $request->session()->put('supply_shop_text', $dp_supply_shop_text);

                $request->session()->put('sale_company_code', $dp_sale_company_code);
                $request->session()->put('sale_company_id', $dp_sale_company_id);
                $request->session()->put('sale_company_text', $dp_sale_company_text);
                $request->session()->put('sale_shop_code', $dp_sale_shop_code);
                $request->session()->put('sale_shop_id', $dp_sale_shop_id);
                $request->session()->put('sale_shop_text', $dp_sale_shop_text);

                $request->session()->put('product_code', $dp_product_code);
                $request->session()->put('product_id', $dp_product_id);
                $request->session()->put('product_text', $dp_product_text);

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

                $dp_product_code  = null;
                $dp_product_id    = null;
                $dp_product_text  = null;
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

            // supply_slip_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($product_id)) {

                $product_sub_query = DB::table('supply_slip_details as SubTable')
                ->select('SubTable.supply_slip_id AS supply_slip_id')
                ->where('SubTable.product_id', '=', $product_id)
                ->groupBy('SubTable.supply_slip_id');
            }

            //---------------------
            // 仕入額を取得
            //---------------------
            $supplySlipList = DB::table('supply_slips AS SupplySlip')

            ->selectRaw('DATE_FORMAT(SupplySlip.date, "Y-m-d")          AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "Y-m-d") AS supply_slip_delivery_date')
            ->selectRaw('SUM(COALESCE(SupplySlip.total,0))              AS supply_daily_amount')

            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id');
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 1, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SupplySlip.date', [$first_date, $last_date]);
            })
            ->if(!empty($first_date) && !empty($last_date) && $dp_date_type == 2, function ($query) use ($first_date, $last_date) {
                return $query->whereBetween('SupplySlip.delivery_date', [$first_date, $last_date]);
            })
            ->if(!empty($supply_company_id), function ($query) use ($dp_supply_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $dp_supply_company_id);
            })
            ->if(!empty($supply_shop_id), function ($query) use ($dp_supply_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $dp_supply_shop_id);
            })
            ->if(!empty($product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SupplySlipDetail'), 'SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id')
                       ->mergeBindings($product_sub_query);
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

                    $supply_date_arr[$supply_date] = [
                        "supply_daily_amount"  => $supplySlipVal->supply_daily_amount
                    ];
                }
            }


            //---------------------
            // 売上額を取得
            //---------------------
            $saleSlipList = DB::table('sale_slips AS SaleSlip')

            ->selectRaw('DATE_FORMAT(SaleSlip.date, "Y-m-d")          AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "Y-m-d") AS sale_slip_delivery_date')
            ->selectRaw('SUM(COALESCE(SaleSlip.total,0))              AS sale_daily_amount')

            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
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
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SaleSlipDetail'), 'SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                       ->mergeBindings($product_sub_query);
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

                    $sale_date_arr[$sale_date] = [
                        "sale_daily_amount"  => $saleSlipVal->sale_daily_amount
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

                if (isset($supply_date_arr[$date_val])) $supply_daily_amount = $supply_date_arr[$sale_date]->supply_daily_amount;
                if (isset($sale_date_arr[$date_val]))   $sale_daily_amount   = $sale_date_arr[$sale_date]->sale_daily_amount;

                $daily_performance_arr = [
                    "date"                => $date_val,
                    "supply_daily_amount" => $supply_daily_amount,
                    "sale_daily_amount"   => $sale_daily_amount,
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
            "date_arr"                          => $date_arr,

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

            "dp_product_code"                   => $dp_product_code,
            "dp_product_id"                     => $dp_product_id,
            "dp_product_text"                   => $dp_product_text,

            "supply_total_amount"               => $supply_total_amount,
            "sale_total_amount"                 => $sale_total_amount,

            "daily_performance_arr"             => $daily_performance_arr,
        ]);
    }
}

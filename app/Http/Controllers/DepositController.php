<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DepositController extends Controller
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
     * 入金一覧
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
            $search_action = '../DepositIndex';
        } else {
            $search_action = './DepositIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type      = $request->session()->get('deposit_condition_date_from');
            $condition_date_from      = $request->session()->get('deposit_condition_date_from');
            $condition_date_to        = $request->session()->get('deposit_condition_date_to');
            $condition_company_code   = $request->session()->get('deposit_condition_company_code');
            $condition_company_id     = $request->session()->get('deposit_condition_company_id');
            $condition_company_text   = $request->session()->get('deposit_condition_company_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type    = $request->data['Deposit']['date_type'];
                $condition_company_code = $request->data['Deposit']['deposit_company_code'];
                $condition_company_id   = $request->data['Deposit']['deposit_company_id'];
                $condition_company_text = $request->data['Deposit']['deposit_company_text'];

                // 日付の設定
                $condition_date_from = $request->data['Deposit']['deposit_date_from'];
                $condition_date_to   = $request->data['Deposit']['deposit_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('deposit_condition_date_type', $condition_date_type);
                $request->session()->put('deposit_condition_date_from', $condition_date_from);
                $request->session()->put('deposit_condition_date_to', $condition_date_to);
                $request->session()->put('deposit_condition_company_code', $condition_company_code);
                $request->session()->put('deposit_condition_company_id', $condition_company_id);
                $request->session()->put('deposit_condition_company_text', $condition_company_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_type      = null;
                $condition_date_from      = null;
                $condition_date_to        = null;
                $condition_sale_date_from = null;
                $condition_sale_date_to   = null;
                $condition_company_code   = null;
                $condition_company_id     = null;
                $condition_company_text   = null;
                $request->session()->forget('deposit_condition_date_type');
                $request->session()->forget('deposit_condition_date_from');
                $request->session()->forget('deposit_condition_date_to');
                $request->session()->forget('deposit_condition_sale_date_from');
                $request->session()->forget('deposit_condition_sale_date_to');
                $request->session()->forget('deposit_condition_company_code');
                $request->session()->forget('deposit_condition_company_id');
                $request->session()->forget('deposit_condition_company_text');

            }
        }

        try {

            // 出金一覧を取得
            $depositList = DB::table('deposits AS Deposit')
            ->select(
                'Deposit.id             AS deposit_id',
                'Deposit.date           AS deposit_date',
                'Deposit.sale_from_date AS sale_from_date',
                'Deposit.sale_to_date   AS sale_to_date',
                'Deposit.amount         AS amount',
                'SaleCompany.name       AS sale_company_name'
            )
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('Deposit.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && $condition_date_type == 2, function ($query) use ($condition_date_from) {
                return $query->where('Deposit.sale_from_date', '>=', $condition_date_from);
            })
            ->if(!empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_to) {
                return $query->where('Deposit.sale_to_date', '<=', $condition_date_to);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('Deposit.sale_company_id', '=', $condition_company_id);
            })
            ->where('Deposit.active', '=', '1')
            ->orderBy('Deposit.date', 'desc')->paginate(5);

        } catch (\Exception $e) {

            dd($e);

            return view('Deposit.index')->with([
                'errorMessage' => $e
            ]);
        }

        // 対象日付のチェック
        $check_str_slip_date = "";
        $check_str_deposit_date = "";
        if($condition_date_type == 2) $check_str_deposit_date = "checked";
        else  $check_str_slip_date = "checked";

        return view('Deposit.index')->with([
            "search_action"            => $search_action,
            "check_str_slip_date"      => $check_str_slip_date,
            "check_str_deposit_date"   => $check_str_deposit_date,
            "condition_date_from"      => $condition_date_from,
            "condition_date_to"        => $condition_date_to,
            "condition_company_code"   => $condition_company_code,
            "condition_company_id"     => $condition_company_id,
            "condition_company_text"   => $condition_company_text,
            "depositList"              => $depositList
        ]);
    }

    /**
     * 入金登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        return view('Deposit.create');
    }

    /**
     * 出金登録処理
     * 
     */
    public function registerDeposit(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $depositDatas       = $request->data['Deposit'];
            $depositDetailDatas = $request->data['DepositDetail'];
// error_log(print_r($depositDatas, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
            // 入力値を配列に格納する
            $insertParams = array(
                'sale_company_id'   => $depositDatas['deposit_company_id'],
                'sale_shop_id'      => $depositDatas['deposit_shop_id'],
                'date'              => $depositDatas['deposit_date'],
                'sale_from_date'    => $depositDatas['sales_from_date'],
                'sale_to_date'      => $depositDatas['sales_to_date'],
                'staff_id'          => $depositDatas['staff_id'],
                'sub_total'         => $depositDatas['price'],
                'adjustment_amount' => $depositDatas['adjustment_price'],
                'amount'            => $depositDatas['total_price'],
                'deposit_method_id' => $depositDatas['deposit_method_id'],
                'remarks'           => $depositDatas['memo'],
                'created_at'        => $user_info_id,
                'created'           => Carbon::now(),
                'updated_at'        => $user_info_id,
                'modified'          => Carbon::now()
            );

            $depositNewId = DB::table('deposits')->insertGetId($insertParams);

            // --------------------------------------
            // 詳細テーブルに登録する(deposit_withdrawal_details)
            // --------------------------------------
            // sale_slip_idの数ループさせる
            foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {

                // 仕入伝票データ取得
                $saleSlipDate    = $depositDetailDatas[$saleSlipId]['date'];
                $notaxSubTotal8  = $depositDetailDatas[$saleSlipId]['notax_subTotal_8'];
                $notaxSubTotal10 = $depositDetailDatas[$saleSlipId]['notax_subTotal_10'];
                $subTotal        = $depositDetailDatas[$saleSlipId]['subTotal'];
                $deliveryPrice   = $depositDetailDatas[$saleSlipId]['delivery_price'];
                $adjustPrice     = $depositDetailDatas[$saleSlipId]['adjust_price'];
                $total           = $depositDetailDatas[$saleSlipId]['total'];

                // 登録データ格納
                $insertDetailParams[] = array(
                    'deposit_withdrawal_id'   => $depositNewId,
                    'supply_sale_slip_id'     => $saleSlipId,
                    'deposit_withdrawal_date' => $depositDatas['deposit_date'],
                    'supply_sale_slip_date'   => $saleSlipDate,
                    'type'                    => 2,
                    'notax_sub_total_8'       => $notaxSubTotal8,
                    'notax_sub_total_10'      => $notaxSubTotal10,
                    'sub_total'               => $subTotal,
                    'delivery_price'          => $deliveryPrice,
                    'adjust_price'            => $adjustPrice,
                    'total'                   => $total,
                    'active'                  => 1,
                    'created_user_id'         => $user_info_id,
                    'created'                 => Carbon::now(),
                    'modified_user_id'        => $user_info_id,
                    'modified'                => Carbon::now(),
                );
            }

            if (!empty($insertDetailParams)) {
                DB::table('deposit_withdrawal_details')->insert($insertDetailParams);
            }

            // -----------------------------
            // 売上データのフラグを売上済みにする
            // -----------------------------
            DB::table('sale_slips')
            ->whereIn('id', $depositDetailDatas['sale_slip_ids'])
            ->update(array('sale_flg' => 1));

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./DepositIndex');
    }

    /**
     * 入金編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($deposit_id)
    {
        // 出金データ取得
        $depositDatas = DB::table('deposits AS Deposit')
        ->select(
            'Deposit.id                AS deposit_id',
            'Deposit.date              AS deposit_date',
            'Deposit.sale_from_date    AS sale_from_date',
            'Deposit.sale_to_date      AS sale_to_date',
            'Deposit.sub_total         AS sub_total',
            'Deposit.adjustment_amount AS adjustment_amount',
            'Deposit.amount            AS amount',
            'Deposit.deposit_method_id AS deposit_method_id',
            'Deposit.staff_id          AS staff_id',
            'Deposit.remarks           AS remarks',
            'Deposit.sale_company_id   AS sale_company_id',
            'Deposit.sale_shop_id      AS sale_shop_id',
            'SaleCompany.name          AS sale_company_name',
            'SaleCompany.code          AS sale_company_code',
            'SaleShop.name             AS sale_shop_name',
            'SaleShop.code             AS sale_shop_code',
            'Staff.code                AS staff_code'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
        })
        ->leftJoin('sale_shops as SaleShop', function ($join) {
            $join->on('SaleShop.id', '=', 'Deposit.sale_shop_id')
                 ->where('SaleShop.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'Deposit.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->where([
            ['Deposit.id', '=', $deposit_id],
            ['Deposit.active', '=', '1']
        ])
        ->first();

        // 仕入データ取得
        $saleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id As id',
            'SaleSlip.date As date',
            'SaleSlip.notax_sub_total_8 As notax_sub_total_8',
            'SaleSlip.notax_sub_total_10 As notax_sub_total_10',
            'SaleSlip.delivery_price As delivery_price',
            'SaleSlip.adjust_price As adjust_price',
            'SaleSlip.sale_flg As sale_flg'
        )
        ->whereBetween('SaleSlip.date', [$depositDatas->sale_from_date, $depositDatas->sale_to_date])
        ->where([
            ['SaleSlip.sale_company_id', '=', $depositDatas->sale_company_id],
            ['SaleSlip.deposit_flg', '=', '0'],
            ['SaleSlip.active', '=', '1']
        ])
        ->get();

        // 出金詳細データ取得
        $depositDetailDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
        ->select(
            'DepositWithdrawalDetail.supply_sale_slip_id As sale_slip_id'
        )
        ->where([
            ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $deposit_id],
            ['DepositWithdrawalDetail.type', '=', '2'],
            ['DepositWithdrawalDetail.active', '=', '1']
        ])
        ->get();

        return view('Deposit.edit')->with([
            'depositDatas'       => $depositDatas,
            'saleSlipDatas'      => $saleSlipList,
            'depositDetailDatas' => $depositDetailDatas,
        ]);
    }

    /**
     * 入金編集処理
     * 
     */
    public function editRegisterDeposit(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $depositDatas       = $request->data['Deposit'];
            $depositDetailDatas = $request->data['DepositDetail'];

            // 入力値を配列に格納する
            $updateParams = array(
                'sale_company_id'   => $depositDatas['deposit_company_id'],
                'sale_shop_id'      => $depositDatas['deposit_shop_id'],
                'date'              => $depositDatas['deposit_date'],
                'sale_from_date'    => $depositDatas['sale_from_date'],
                'sale_to_date'      => $depositDatas['sale_to_date'],
                'staff_id'          => $depositDatas['staff_id'],
                'sub_total'         => $depositDatas['price'],
                'adjustment_amount' => $depositDatas['adjustment_price'],
                'amount'            => $depositDatas['total_price'],
                'deposit_method_id' => $depositDatas['deposit_method_id'],
                'remarks'           => $depositDatas['memo'],
                'updated_at'        => $user_info_id,
                'modified'          => Carbon::now()
            );

            // 更新処理
            DB::table('deposits')
            ->where('id', '=', $depositDatas['id'])
            ->update($updateParams);

            // -------------------------------------
            // 入出金詳細テーブルのデータを削除して新規登録
            // -------------------------------------
            // データ削除前に支払フラグ戻すように仕入IDを取得しておく
            $delBeforeSaleSlipIds = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
            ->select(
                'DepositWithdrawalDetail.supply_sale_slip_id As sale_slip_id'
            )
            ->where([
                ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $depositDatas['id']],
                ['DepositWithdrawalDetail.type', '=', '2'],
                ['DepositWithdrawalDetail.active', '=', '1']
            ])
            ->get();

            // データ削除
            \App\DepositWithdrawalDetail::where('deposit_withdrawal_id', $depositDatas['id'])->delete();

            // -------
            // 新規登録
            // -------
            // sale_slip_idの数ループさせる
            foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {

                // 仕入伝票データ取得
                $saleSlipDate    = $depositDetailDatas[$saleSlipId]['date'];
                $notaxSubTotal8  = $depositDetailDatas[$saleSlipId]['notax_subTotal_8'];
                $notaxSubTotal10 = $depositDetailDatas[$saleSlipId]['notax_subTotal_10'];
                $subTotal        = $depositDetailDatas[$saleSlipId]['subTotal'];
                $deliveryPrice   = $depositDetailDatas[$saleSlipId]['delivery_price'];
                $adjustPrice     = $depositDetailDatas[$saleSlipId]['adjust_price'];
                $total           = $depositDetailDatas[$saleSlipId]['total'];

                // 登録データ格納
                $insertDetailParams[] = array(
                    'deposit_withdrawal_id'   => $depositDatas['id'],
                    'supply_sale_slip_id'     => $saleSlipId,
                    'deposit_withdrawal_date' => $depositDatas['deposit_date'],
                    'supply_sale_slip_date'   => $saleSlipDate,
                    'type'                    => 2,
                    'notax_sub_total_8'       => $notaxSubTotal8,
                    'notax_sub_total_10'      => $notaxSubTotal10,
                    'sub_total'               => $subTotal,
                    'delivery_price'          => $deliveryPrice,
                    'adjust_price'            => $adjustPrice,
                    'total'                   => $total,
                    'active'                  => 1,
                    'created_user_id'         => $user_info_id,
                    'created'                 => Carbon::now(),
                    'modified_user_id'        => $user_info_id,
                    'modified'                => Carbon::now(),
                );
            }

            if (!empty($insertDetailParams)) {
                DB::table('deposit_withdrawal_details')->insert($insertDetailParams);
            }

            // -----------------------------
            // 支払データのフラグを支払済みにする
            // -----------------------------
            // 一旦対象データの支払フラグを未払いに戻す
            foreach ($delBeforeSaleSlipIds as $delBeforeSaleSlipId) {
                $saleSlipIds[] = $delBeforeSaleSlipId->sale_slip_id;
            }
            DB::table('sale_slips')
            ->whereIn('id', $saleSlipIds)
            ->update(array('sale_flg' => 0));

            // その後支払済みに更新する
            DB::table('sale_slips')
            ->whereIn('id', $depositDetailDatas['sale_slip_ids'])
            ->update(array('sale_flg' => 1));

            // --------------------------------------------
            // 入金詳細テーブルに重複しているIDが存在しているか確認
            // --------------------------------------------
            $delTargetDatas = array();
            foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {
                // 編集対象のID以外のものを抽出
                $delTargetDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                ->select(
                    'DepositWithdrawalDetail.id As id',
                    'DepositWithdrawalDetail.deposit_withdrawal_id As deposit_withdrawal_id'
                )
                ->join('sale_slips As SaleSlip', function ($join){
                    $join->on('DepositWithdrawalDetail.supply_sale_slip_id', '=', 'SaleSlip.id');
                })
                ->where([
                    ['DepositWithdrawalDetail.deposit_withdrawal_id', '<>', $depositDatas['id']],
                    ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $saleSlipId],
                    ['SaleSlip.deposit_flg', '=', '0'],
                    ['DepositWithdrawalDetail.active', '=', '1'],
                    ['SaleSlip.active', '=', '1']
                ])
                ->get();

                // ----------------------------------------
                // 存在していれば編集対象以外のデータを削除、再計算
                // ----------------------------------------
                if (!$delTargetDatas->isEmpty()) {

                    // 削除対象データを削除
                    \App\DepositWithdrawalDetail::where('id', $delTargetDatas[0]->id)->delete();

                    // ------------------------
                    // 削除された出金IDの再計算する
                    // ------------------------
                    // まずは計算データ取得
                    $depositDetailCalcDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                    ->select(
                        'DepositWithdrawalDetail.notax_sub_total_8 As notax_sub_total_8',
                        'DepositWithdrawalDetail.notax_sub_total_10 As notax_sub_total_10',
                        'DepositWithdrawalDetail.delivery_price As delivery_price',
                        'DepositWithdrawalDetail.adjust_price As adjust_price'
                    )
                    ->where([
                        ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $delTargetDatas[0]->deposit_withdrawal_id],
                        ['DepositWithdrawalDetail.active', '=', '1']
                    ])
                    ->get();

                    $depositCalcDatas = DB::table('deposits AS Deposit')
                    ->select(
                        'Deposit.adjustment_amount As adjustment_amount'
                    )
                    ->where([
                        ['Deposit.id', '=', $delTargetDatas[0]->deposit_withdrawal_id],
                        ['Deposit.active', '=', '1']
                    ])
                    ->get();

                    // ---------
                    // データ計算
                    // ---------
                    $notaxSubTotal8   = 0;
                    $notaxSubTotal10  = 0;
                    $subTotal8        = 0;
                    $subTotal10       = 0;
                    $deliveryPrice    = 0;
                    $adjustTotalPrice = 0;
                    $adjustPrice      = 0;
                    $subTotal         = 0;
                    $total            = 0;
                    foreach ($depositDetailCalcDatas as $depositDetailCalcData) {

                        // 税抜8％額
                        $notaxSubTotal8 += $depositDetailCalcData->notax_sub_total_8;

                        // 税抜10％額
                        $notaxSubTotal10 += $depositDetailCalcData->notax_sub_total_10;

                        // 配送額
                        $deliveryPrice += $depositDetailCalcData->delivery_price;

                        // 調整額
                        $adjustTotalPrice += $depositDetailCalcData->adjust_price;

                    }

                    // 税込8%
                    $subTotal8 = round($notaxSubTotal8 * 1.08);

                    // 税込10%
                    $subTotal10 = round($notaxSubTotal10 * 1.1);

                    // 小計(調整額含まない)
                    $subTotal = $subTotal8 + $subTotal10 + $deliveryPrice + $adjustTotalPrice;

                    // 総合計
                    if (!empty($depositCalcDatas[0]->adjustment_amount)) $adjustPrice = $depositCalcDatas[0]->adjustment_amount;
                    $total = $subTotal + $adjustPrice;

                    // -----------------------------
                    // 計算結果を出金テーブルに更新させる
                    // -----------------------------
                    DB::table('deposits')
                    ->where('id', $delTargetDatas[0]->deposit_withdrawal_id)
                    ->update(array(
                        'sub_total' => $subTotal,
                        'amount'    => $total
                    ));
                }

            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./DepositIndex');
    }

    /**
     * ajax処理
     * 指定された範囲の請求伝票一覧を返す
     * 
     */
    public function AjaxSearchSaleSlips(Request $request) {

        // 入力された値を取得
        $saleFromDate  = $request->sales_from_date;
        $saleToDate    = $request->sales_to_date;
        $saleCompany   = $request->sales_company;
        $searchDateVal = $request->search_date_val;
        $action        = $request->action;
// error_log(print_r('aaa', true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
        // 入力された値のチェック
        if (empty($saleFromDate) && empty($saleToDate)) {
            return false;
        }

        if (empty($saleCompany)) {
            return false;
        }

        // どちらか片方にしか日付が入っていない場合は入っている方の日付と同じ日付を設定する
        if (!empty($saleFromDate) && empty($saleToDate)) {
            $saleToDate = $saleFromDate;
        }

        if (empty($saleFromDate) && !empty($saleToDate)) {
            $saleFromDate = $saleToDate;
        }

        if ($action == 'edit') {
            $whereArray = [
                ['SaleSlip.sale_company_id', '=', $saleCompany],
                ['SaleSlip.deposit_flg', '=', '0'],
                ['SaleSlip.active', '=', '1']
            ];
        } else {
            $whereArray = [
                ['SaleSlip.sale_company_id', '=', $saleCompany],
                ['SaleSlip.sale_flg', '=', '0'],
                ['SaleSlip.deposit_flg', '=', '0'],
                ['SaleSlip.active', '=', '1']
            ];
        }

        // 対象日付から売上伝票を取得
        $saleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id As id',
            'SaleSlip.date As date',
            'SaleSlip.notax_sub_total_8 As notax_sub_total_8',
            'SaleSlip.notax_sub_total_10 As notax_sub_total_10',
            'SaleSlip.delivery_price As delivery_price',
            'SaleSlip.adjust_price As adjust_price'
        )
        ->if($searchDateVal == 1, function ($query) use ($saleFromDate, $saleToDate){
            return $query->whereBetween('SaleSlip.date', [$saleFromDate, $saleToDate]);
        })
        ->if($searchDateVal == 2, function ($query) use ($saleFromDate, $saleToDate){
            return $query->whereBetween('SaleSlip.delivery_date', [$saleFromDate, $saleToDate]);
        })
        ->where($whereArray)
        ->get();

        // 取得してきたデータを計算してHTMLを形成
        // HTML格納変数初期化
        $ajaxHtml = '';
        if (!$saleSlipList->isEmpty()) {

            $notaxSubTotal8  = 0;
            $notaxSubTotal10 = 0;
            $tax8            = 0;
            $tax10           = 0;
            $subTotal        = 0;
            $delivery_price  = 0;
            $adjust_price    = 0;
            $total           = 0;

            $ajaxHtml .= '<table class="result-table" onchange="javascript:changeCalcFlg()">';
            $ajaxHtml .= '    <tr>';
            $ajaxHtml .= '        <th class="width-5">選択</th>';
            $ajaxHtml .= '        <th>伝票日付</th>';
            $ajaxHtml .= '        <th>8%税抜合計</th>';
            $ajaxHtml .= '        <th>外税8%</th>';
            $ajaxHtml .= '        <th>10%税抜合計</th>';
            $ajaxHtml .= '        <th>外税10%</th>';
            $ajaxHtml .= '        <th>小計</th>';
            $ajaxHtml .= '        <th>配送額</th>';
            $ajaxHtml .= '        <th>調整額</th>';
            $ajaxHtml .= '        <th>調整後金額</th>';
            $ajaxHtml .= '        <th class="width-5">明細</th>';
            $ajaxHtml .= '    </tr>';

            foreach ($saleSlipList as $saleSlipDatas) {
                // 金額計算
                $notaxSubTotal8  = $saleSlipDatas->notax_sub_total_8;                     // 税抜8%金額
                $tax8            = round($saleSlipDatas->notax_sub_total_8 * 8 / 100);    // 8%消費税
                $notaxSubTotal10 = $saleSlipDatas->notax_sub_total_10;                    // 税抜10%金額
                $tax10           = round($saleSlipDatas->notax_sub_total_10 * 10 / 100);  // 8%消費税
                $subTotal        = $notaxSubTotal8 + $tax8 + $notaxSubTotal10 + $tax10;   // 小計
                $delivery_price  = $saleSlipDatas->delivery_price;                        // 配送額
                $adjust_price    = $saleSlipDatas->adjust_price;                          // 調整額
                $total           = $subTotal + $delivery_price + $adjust_price;           // 調整後総合計額

                $ajaxHtml .= '        <tr>';
                $ajaxHtml .= '            <td><input type="checkbox" id="sale-slip-id-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][id]" value="' . $saleSlipDatas->id . '" onchange="javascript:discardSaleSlipId(' . $saleSlipDatas->id . ')"></td>';
                $ajaxHtml .= '            <td>' . $saleSlipDatas->date;
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][date]" value="' . $saleSlipDatas->date . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal8);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-subTotal8-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][notax_subTotal_8]" value="' . $notaxSubTotal8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax8);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][tax8]" value="' . $tax8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal10);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-subTotal10-' . $saleSlipDatas->id . '"name="data[DepositDetail][' . $saleSlipDatas->id . '][notax_subTotal_10]" value="' . $notaxSubTotal10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax10);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][tax10]" value="' . $tax10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($subTotal);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][subTotal]" value="' . $subTotal . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($delivery_price);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-deliveryPrice-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][delivery_price]" value="' . $delivery_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($adjust_price);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-adjustPrice-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][adjust_price]" value="' . $adjust_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td id="sale-slip-total' . $saleSlipDatas->id . '" data-value="' . $total . '">' . number_format($total);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][total]" value="' . $total . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>';
                $ajaxHtml .= '                <a href="./SaleSlipEdit/' . $saleSlipDatas->id . '">明細</a>';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '        </tr>';

            }

            $ajaxHtml .= '</table>';

        }

        $returnArray = array($ajaxHtml);
        return $returnArray;

    }

}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use Carbon\Carbon;

class WithdrawalController extends Controller
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
     * 出金一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // $data = session()->all();
        // error_log(print_r($data, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');

        // $user_info    = \Auth::user();
        // error_log(print_r($user_info, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if(count($path_array) > 1){
            $search_action = '../WithdrawalIndex';
        } else {
            $search_action = './WithdrawalIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type         = $request->session()->get('withdrawal_condition_date_from');
            $condition_date_from         = $request->session()->get('withdrawal_condition_date_from');
            $condition_date_to           = $request->session()->get('withdrawal_condition_date_to');
            $condition_company_code      = $request->session()->get('withdrawal_condition_company_code');
            $condition_company_id        = $request->session()->get('withdrawal_condition_company_id');
            $condition_company_text      = $request->session()->get('withdrawal_condition_company_text');
            // $condition_shop_code         = $request->session()->get('condition_shop_code');
            // $condition_shop_id           = $request->session()->get('condition_shop_id');
            // $condition_shop_text         = $request->session()->get('condition_shop_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type     = $request->data['Withdrawal']['date_type'];
                $condition_company_code  = $request->data['Withdrawal']['withdrawal_company_code'];
                $condition_company_id    = $request->data['Withdrawal']['withdrawal_company_id'];
                $condition_company_text  = $request->data['Withdrawal']['withdrawal_company_text'];
                // $condition_shop_code     = $request->data['Withdrawal']['withdrawal_shop_code'];
                // $condition_shop_id       = $request->data['Withdrawal']['withdrawal_shop_id'];
                // $condition_shop_text     = $request->data['Withdrawal']['withdrawal_shop_text'];

                // 日付の設定
                $condition_date_from = $request->data['Withdrawal']['withdrawal_date_from'];
                $condition_date_to   = $request->data['Withdrawal']['withdrawal_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('withdrawal_condition_date_type', $condition_date_type);
                $request->session()->put('withdrawal_condition_date_from', $condition_date_from);
                $request->session()->put('withdrawal_condition_date_to', $condition_date_to);
                $request->session()->put('withdrawal_condition_company_code', $condition_company_code);
                $request->session()->put('withdrawal_condition_company_id', $condition_company_id);
                $request->session()->put('withdrawal_condition_company_text', $condition_company_text);
                // $request->session()->put('condition_shop_code', $condition_shop_code);
                // $request->session()->put('condition_shop_id', $condition_shop_id);
                // $request->session()->put('condition_shop_text', $condition_shop_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_type         = null;
                $condition_date_from         = null;
                $condition_date_to           = null;
                $condition_company_code      = null;
                $condition_company_id        = null;
                $condition_company_text      = null;
                // $condition_shop_code         = null;
                // $condition_shop_id           = null;
                // $condition_shop_text         = null;
                $request->session()->forget('withdrawal_condition_date_type');
                $request->session()->forget('withdrawal_condition_date_from');
                $request->session()->forget('withdrawal_condition_date_to');
                $request->session()->forget('withdrawal_condition_company_code');
                $request->session()->forget('withdrawal_condition_company_id');
                $request->session()->forget('withdrawal_condition_company_text');
                // $request->session()->forget('condition_shop_code');
                // $request->session()->forget('condition_shop_id');
                // $request->session()->forget('condition_shop_text');

            }
        }

        try {

            // 出金一覧を取得
            $withdrawalList = DB::table('withdrawals AS Withdrawal')
            ->select(
                'Withdrawal.id                AS withdrawal_id',
                'Withdrawal.date              AS withdrawal_date',
                'Withdrawal.payment_from_date AS payment_from_date',
                'Withdrawal.payment_to_date   AS payment_to_date',
                'Withdrawal.amount            AS amount',
                'SupplyCompany.name           AS supply_company_name'
            )
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'Withdrawal.supply_company_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('Withdrawal.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && $condition_date_type == 2, function ($query) use ($condition_date_from) {
                return $query->where('Withdrawal.payment_from_date', '>=', $condition_date_from);
            })
            ->if(!empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_to) {
                return $query->where('Withdrawal.payment_to_date', '<=', $condition_date_to);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('Withdrawal.supply_company_id', '=', $condition_company_id);
            })
            ->where('Withdrawal.active', '=', '1')
            ->orderBy('Withdrawal.date', 'desc')->paginate(5);

        } catch (\Exception $e) {

            dd($e);

            return view('Withdrawal.index')->with([
                'errorMessage' => $e
            ]);
        }

        // 対象日付のチェック
        $check_str_slip_date = "";
        $check_str_withdrawal_date = "";
        if($condition_date_type == 2) $check_str_withdrawal_date = "checked";
        else  $check_str_slip_date = "checked";

        return view('Withdrawal.index')->with([
            "search_action"               => $search_action,
            "check_str_slip_date"         => $check_str_slip_date,
            "check_str_withdrawal_date"   => $check_str_withdrawal_date,
            "condition_date_from"         => $condition_date_from,
            "condition_date_to"           => $condition_date_to,
            "condition_company_code"      => $condition_company_code,
            "condition_company_id"        => $condition_company_id,
            "condition_company_text"      => $condition_company_text,
            // "condition_shop_code"         => $condition_shop_code,
            // "condition_shop_id"           => $condition_shop_id,
            // "condition_shop_text"         => $condition_shop_text,
            "withdrawalList"              => $withdrawalList
        ]);
    }

    /**
     * 出金登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        return view('Withdrawal.create');
    }

    /**
     * 出金編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($withdrawal_id)
    {
        // 出金データ取得
        $withdrawalDatas = DB::table('withdrawals AS Withdrawal')
        ->select(
            'Withdrawal.id                AS withdrawal_id',
            'Withdrawal.date              AS withdrawal_date',
            'Withdrawal.payment_from_date AS payment_from_date',
            'Withdrawal.payment_to_date   AS payment_to_date',
            'Withdrawal.sub_total         AS sub_total',
            'Withdrawal.adjustment_amount AS adjustment_amount',
            'Withdrawal.tax_id            AS tax_id',
            'Withdrawal.amount            AS amount',
            'Withdrawal.payment_method_id AS payment_method_id',
            'Withdrawal.staff_id          AS staff_id',
            'Withdrawal.remarks           AS remarks',
            'Withdrawal.supply_company_id AS supply_company_id',
            'Withdrawal.supply_shop_id    AS supply_shop_id',
            'SupplyCompany.name           AS supply_company_name',
            'SupplyCompany.code           AS supply_company_code',
            'SupplyShop.name              AS supply_shop_name',
            'SupplyShop.code              AS supply_shop_code',
            'Staff.code                   AS staff_code'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('supply_companies AS SupplyCompany', function ($join) {
            $join->on('SupplyCompany.id', '=', 'Withdrawal.supply_company_id');
        })
        ->leftJoin('supply_shops as SupplyShop', function ($join) {
            $join->on('SupplyShop.id', '=', 'Withdrawal.supply_shop_id')
                 ->where('SupplyShop.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'Withdrawal.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->where([
            ['Withdrawal.id', '=', $withdrawal_id],
            ['Withdrawal.active', '=', '1']
        ])
        ->first();

        // 仕入データ取得
        $supplySlipList = DB::table('supply_slips AS SupplySlip')
        ->select(
            'SupplySlip.id As id',
            'SupplySlip.date As date',
            'SupplySlip.notax_sub_total_8 As notax_sub_total_8',
            'SupplySlip.notax_sub_total_10 As notax_sub_total_10',
            'SupplySlip.delivery_price As delivery_price',
            'SupplySlip.adjust_price As adjust_price',
            'SupplySlip.payment_flg As payment_flg'
        )
        ->whereBetween('SupplySlip.date', [$withdrawalDatas->payment_from_date, $withdrawalDatas->payment_to_date])
        ->where([
            ['SupplySlip.supply_company_id', '=', $withdrawalDatas->supply_company_id],
            ['SupplySlip.withdrawal_flg', '=', '0'],
            ['SupplySlip.active', '=', '1']
        ])
        ->get();

        // 出金詳細データ取得
        $withdrawalDetailDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
        ->select(
            'DepositWithdrawalDetail.supply_sale_slip_id As supply_slip_id'
        )
        ->where([
            ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $withdrawal_id],
            ['DepositWithdrawalDetail.type', '=', '1'],
            ['DepositWithdrawalDetail.active', '=', '1']
        ])
        ->get();

        return view('Withdrawal.edit')->with([
            'withdrawalDatas'       => $withdrawalDatas,
            'supplySlipDatas'       => $supplySlipList,
            'withdrawalDetailDatas' => $withdrawalDetailDatas,
        ]);
    }

    /**
     * 出金登録処理
     * 
     */
    public function registerWithdrawal(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $withdrawalDatas       = $request->data['Withdrawal'];
            $withdrawalDetailDatas = $request->data['WithdrawalDetail'];
// error_log(print_r($withdrawalDetailDatas, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
            // 入力値を配列に格納する
            $insertParams = array(
                'supply_company_id' => $withdrawalDatas['withdrawal_company_id'],
                'supply_shop_id'    => $withdrawalDatas['withdrawal_shop_id'],
                'date'              => $withdrawalDatas['withdrawal_date'],
                'payment_from_date' => $withdrawalDatas['payment_from_date'],
                'payment_to_date'   => $withdrawalDatas['payment_to_date'],
                'staff_id'          => $withdrawalDatas['staff_id'],
                'sub_total'         => $withdrawalDatas['price'],
                'adjustment_amount' => $withdrawalDatas['adjustment_price'],
                'tax_id'            => 1,
                'amount'            => $withdrawalDatas['total_price'],
                'payment_method_id' => $withdrawalDatas['payment_method_id'],
                'remarks'           => $withdrawalDatas['memo'],
                'created_at'        => $user_info_id,
                'created'           => Carbon::now(),
                'updated_at'        => $user_info_id,
                'modified'          => Carbon::now()
            );

            $withdrawalNewId = DB::table('withdrawals')->insertGetId($insertParams);

            // --------------------------------------
            // 詳細テーブルに登録する(deposit_withdrawal_details)
            // --------------------------------------
            // supply_slip_idの数ループさせる
            foreach ($withdrawalDetailDatas['supply_slip_ids'] as $supplySlipId) {

                // 仕入伝票データ取得
                $supplySlipDate  = $withdrawalDetailDatas[$supplySlipId]['date'];
                $notaxSubTotal8  = $withdrawalDetailDatas[$supplySlipId]['notax_subTotal_8'];
                $notaxSubTotal10 = $withdrawalDetailDatas[$supplySlipId]['notax_subTotal_10'];
                $subTotal        = $withdrawalDetailDatas[$supplySlipId]['subTotal'];
                $deliveryPrice   = $withdrawalDetailDatas[$supplySlipId]['delivery_price'];
                $adjustPrice     = $withdrawalDetailDatas[$supplySlipId]['adjust_price'];
                $total           = $withdrawalDetailDatas[$supplySlipId]['total'];

                // 登録データ格納
                $insertDetailParams[] = array(
                    'deposit_withdrawal_id'   => $withdrawalNewId,
                    'supply_sale_slip_id'     => $supplySlipId,
                    'deposit_withdrawal_date' => $withdrawalDatas['withdrawal_date'],
                    'supply_sale_slip_date'   => $supplySlipDate,
                    'type'                    => 1,
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
            // 仕入データのフラグを支払済みにする
            // -----------------------------
            DB::table('supply_slips')
            ->whereIn('id', $withdrawalDetailDatas['supply_slip_ids'])
            ->update(array('payment_flg' => 1));

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./WithdrawalIndex');
    }

    /**
     * 出金編集処理
     * 
     */
    public function editRegisterWithdrawal(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $withdrawalDatas       = $request->data['Withdrawal'];
            $withdrawalDetailDatas = $request->data['WithdrawalDetail'];

            // 入力値を配列に格納する
            $updateParams = array(
                'supply_company_id' => $withdrawalDatas['withdrawal_company_id'],
                'supply_shop_id'    => $withdrawalDatas['withdrawal_shop_id'],
                'date'              => $withdrawalDatas['withdrawal_date'],
                'payment_from_date' => $withdrawalDatas['payment_from_date'],
                'payment_to_date'   => $withdrawalDatas['payment_to_date'],
                'staff_id'          => $withdrawalDatas['staff_id'],
                'sub_total'         => $withdrawalDatas['price'],
                'adjustment_amount' => $withdrawalDatas['adjustment_price'],
                'tax_id'            => 1,
                'amount'            => $withdrawalDatas['total_price'],
                'payment_method_id' => $withdrawalDatas['payment_method_id'],
                'remarks'           => $withdrawalDatas['memo'],
                'updated_at'        => $user_info_id,
                'modified'          => Carbon::now()
            );

            // 更新処理
            DB::table('withdrawals')
            ->where('id', '=', $withdrawalDatas['id'])
            ->update($updateParams);

            // -------------------------------------
            // 入出金詳細テーブルのデータを削除して新規登録
            // -------------------------------------
            // データ削除前に支払フラグ戻すように仕入IDを取得しておく
            $delBeforeSupplySlipIds = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
            ->select(
                'DepositWithdrawalDetail.supply_sale_slip_id As supply_slip_id'
            )
            ->where([
                ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $withdrawalDatas['id']],
                ['DepositWithdrawalDetail.type', '=', '1'],
                ['DepositWithdrawalDetail.active', '=', '1']
            ])
            ->get();

            // データ削除
            \App\DepositWithdrawalDetail::where('deposit_withdrawal_id', $withdrawalDatas['id'])->delete();

            // -------
            // 新規登録
            // -------
            // supply_slip_idの数ループさせる
            foreach ($withdrawalDetailDatas['supply_slip_ids'] as $supplySlipId) {

                // 仕入伝票データ取得
                $supplySlipDate  = $withdrawalDetailDatas[$supplySlipId]['date'];
                $notaxSubTotal8  = $withdrawalDetailDatas[$supplySlipId]['notax_subTotal_8'];
                $notaxSubTotal10 = $withdrawalDetailDatas[$supplySlipId]['notax_subTotal_10'];
                $subTotal        = $withdrawalDetailDatas[$supplySlipId]['subTotal'];
                $deliveryPrice   = $withdrawalDetailDatas[$supplySlipId]['delivery_price'];
                $adjustPrice     = $withdrawalDetailDatas[$supplySlipId]['adjust_price'];
                $total           = $withdrawalDetailDatas[$supplySlipId]['total'];

                // 登録データ格納
                $insertDetailParams[] = array(
                    'deposit_withdrawal_id'   => $withdrawalDatas['id'],
                    'supply_sale_slip_id'     => $supplySlipId,
                    'deposit_withdrawal_date' => $withdrawalDatas['withdrawal_date'],
                    'supply_sale_slip_date'   => $supplySlipDate,
                    'type'                    => 1,
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
            foreach ($delBeforeSupplySlipIds as $delBeforeSupplySlipId) {
                $supplySlipIds[] = $delBeforeSupplySlipId->supply_slip_id;
            }
            DB::table('supply_slips')
            ->whereIn('id', $supplySlipIds)
            ->update(array('payment_flg' => 0));

            // その後支払済みに更新する
            DB::table('supply_slips')
            ->whereIn('id', $withdrawalDetailDatas['supply_slip_ids'])
            ->update(array('payment_flg' => 1));

            // --------------------------------------------
            // 出金詳細テーブルに重複しているIDが存在しているか確認
            // --------------------------------------------
            $delTargetDatas = array();
            foreach ($withdrawalDetailDatas['supply_slip_ids'] as $supplySlipId) {
                // 編集対象のID以外のものを抽出
                $delTargetDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                ->select(
                    'DepositWithdrawalDetail.id As id',
                    'DepositWithdrawalDetail.deposit_withdrawal_id As deposit_withdrawal_id'
                )
                ->join('supply_slips As SupplySlip', function ($join){
                    $join->on('DepositWithdrawalDetail.supply_sale_slip_id', '=', 'SupplySlip.id');
                })
                ->where([
                    ['DepositWithdrawalDetail.deposit_withdrawal_id', '<>', $withdrawalDatas['id']],
                    ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $supplySlipId],
                    ['SupplySlip.withdrawal_flg', '=', '0'],
                    ['DepositWithdrawalDetail.active', '=', '1'],
                    ['SupplySlip.active', '=', '1']
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
                    $withdrawalDetailCalcDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
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

                    $withdrawalCalcDatas = DB::table('withdrawals AS Withdrawal')
                    ->select(
                        'Withdrawal.adjustment_amount As adjustment_amount'
                    )
                    ->where([
                        ['Withdrawal.id', '=', $delTargetDatas[0]->deposit_withdrawal_id],
                        ['Withdrawal.active', '=', '1']
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
                    foreach ($withdrawalDetailCalcDatas as $withdrawalDetailCalcData) {

                        // 税抜8％額
                        $notaxSubTotal8 += $withdrawalDetailCalcData->notax_sub_total_8;

                        // 税抜10％額
                        $notaxSubTotal10 += $withdrawalDetailCalcData->notax_sub_total_10;

                        // 配送額
                        $deliveryPrice += $withdrawalDetailCalcData->delivery_price;

                        // 調整額
                        $adjustTotalPrice += $withdrawalDetailCalcData->adjust_price;

                    }

                    // 税込8%
                    $subTotal8 = round($notaxSubTotal8 * 1.08);

                    // 税込10%
                    $subTotal10 = round($notaxSubTotal10 * 1.1);

                    // 小計(調整額含まない)
                    $subTotal = $subTotal8 + $subTotal10 + $deliveryPrice + $adjustTotalPrice;

                    // 総合計
                    if (!empty($withdrawalCalcDatas[0]->adjustment_amount)) $adjustPrice = $withdrawalCalcDatas[0]->adjustment_amount;
                    $total = $subTotal + $adjustPrice;

                    // -----------------------------
                    // 計算結果を出金テーブルに更新させる
                    // -----------------------------
                    DB::table('withdrawals')
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

        return redirect('./WithdrawalIndex');
    }

    /**
     * ajax処理
     * 指定された範囲の出金金額を計算して返す
     * 
     */
    public function AjaxSearchSupplySlips(Request $request) {

        // 入力された値を取得
        $paymentFromDate = $request->payment_from_date;
        $paymentToDate   = $request->payment_to_date;
        $paymentCompany  = $request->payment_company;
        $searchDateVal   = $request->search_date_val;
        $action          = $request->action;

        // 入力された値のチェック
        if (empty($paymentFromDate) && empty($paymentToDate)) {
            return false;
        }

        if (empty($paymentCompany)) {
            return false;
        }

        // どちらか片方にしか日付が入っていない場合は入っている方の日付と同じ日付を設定する
        if (!empty($paymentFromDate) && empty($paymentToDate)) {
            $paymentToDate = $paymentFromDate;
        }

        if (empty($paymentFromDate) && !empty($paymentToDate)) {
            $paymentFromDate = $paymentToDate;
        }

        if ($action == 'edit') {
            $whereArray = [
                ['SupplySlip.supply_company_id', '=', $paymentCompany],
                ['SupplySlip.withdrawal_flg', '=', '0'],
                ['SupplySlip.active', '=', '1']
            ];
        } else {
            $whereArray = [
                ['SupplySlip.supply_company_id', '=', $paymentCompany],
                ['SupplySlip.payment_flg', '=', '0'],
                ['SupplySlip.withdrawal_flg', '=', '0'],
                ['SupplySlip.active', '=', '1']
            ];
        }

        // 対象日付から支払金額を取得
        $supplySlipList = DB::table('supply_slips AS SupplySlip')
        ->select(
            'SupplySlip.id As id',
            'SupplySlip.date As date',
            'SupplySlip.notax_sub_total_8 As notax_sub_total_8',
            'SupplySlip.notax_sub_total_10 As notax_sub_total_10',
            'SupplySlip.delivery_price As delivery_price',
            'SupplySlip.adjust_price As adjust_price'
        )
        ->if($searchDateVal == 1, function ($query) use ($paymentFromDate, $paymentToDate){
            return $query->whereBetween('SupplySlip.date', [$paymentFromDate, $paymentToDate]);
        })
        ->if($searchDateVal == 2, function ($query) use ($paymentFromDate, $paymentToDate){
            return $query->whereBetween('SupplySlip.delivery_date', [$paymentFromDate, $paymentToDate]);
        })
        ->where($whereArray)
        ->get();

        // 取得してきたデータを計算してHTMLを形成
        // HTML格納変数初期化
        $ajaxHtml = '';
        if (!$supplySlipList->isEmpty()) {

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

            foreach ($supplySlipList as $supplySlipDatas) {
                // 金額計算
                $notaxSubTotal8  = $supplySlipDatas->notax_sub_total_8;                     // 税抜8%金額
                $tax8            = round($supplySlipDatas->notax_sub_total_8 * 8 / 100);    // 8%消費税
                $notaxSubTotal10 = $supplySlipDatas->notax_sub_total_10;                    // 税抜10%金額
                $tax10           = round($supplySlipDatas->notax_sub_total_10 * 10 / 100);  // 8%消費税
                $subTotal        = $notaxSubTotal8 + $tax8 + $notaxSubTotal10 + $tax10;     // 小計
                $delivery_price  = $supplySlipDatas->delivery_price;                        // 配送額
                $adjust_price    = $supplySlipDatas->adjust_price;                          // 調整額
                $total           = $subTotal + $delivery_price + $adjust_price;             // 調整後総合計額

                $ajaxHtml .= '        <tr>';
                $ajaxHtml .= '            <td><input type="checkbox" id="supply-slip-id-' . $supplySlipDatas->id . '" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][id]" value="' . $supplySlipDatas->id . '" onchange="javascript:discardSupplySlipId(' . $supplySlipDatas->id . ')"></td>';
                $ajaxHtml .= '            <td>' . $supplySlipDatas->date;
                $ajaxHtml .= '                <input type="hidden" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][date]" value="' . $supplySlipDatas->date . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal8);
                $ajaxHtml .= '                <input type="hidden" id="supply-slip-subTotal8-' . $supplySlipDatas->id . '" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][notax_subTotal_8]" value="' . $notaxSubTotal8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax8);
                $ajaxHtml .= '                <input type="hidden" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][tax8]" value="' . $tax8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal10);
                $ajaxHtml .= '                <input type="hidden" id="supply-slip-subTotal10-' . $supplySlipDatas->id . '"name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][notax_subTotal_10]" value="' . $notaxSubTotal10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax10);
                $ajaxHtml .= '                <input type="hidden" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][tax10]" value="' . $tax10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($subTotal);
                $ajaxHtml .= '                <input type="hidden" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][subTotal]" value="' . $subTotal . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($delivery_price);
                $ajaxHtml .= '                <input type="hidden" id="supply-slip-deliveryPrice-' . $supplySlipDatas->id . '" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][delivery_price]" value="' . $delivery_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($adjust_price);
                $ajaxHtml .= '                <input type="hidden" id="supply-slip-adjustPrice-' . $supplySlipDatas->id . '" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][adjust_price]" value="' . $adjust_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td id="supply-slip-total' . $supplySlipDatas->id . '" data-value="' . $total . '">' . number_format($total);
                $ajaxHtml .= '                <input type="hidden" name="data[WithdrawalDetail][' . $supplySlipDatas->id . '][total]" value="' . $total . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>';
                $ajaxHtml .= '                <a href="./SupplySlipEdit/' . $supplySlipDatas->id . '">明細</a>';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '        </tr>';

            }

            $ajaxHtml .= '</table>';

        }

        $returnArray = array($ajaxHtml);
        return $returnArray;

    }
}
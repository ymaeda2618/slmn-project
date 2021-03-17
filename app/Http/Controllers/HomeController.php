<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // トップ画面の営業情報を取得する
        try {

            // システム年月の月初と月末を求める
            $today        = date('Y-m-d');
            $firstOfMonth = date('Y-m-01');
            $endOfMonth   = date('Y-m-t');
            // $today        = '2020-11-07';
            // $firstOfMonth = '2020-11-01';
            // $endOfMonth   = '2020-11-30';

            // -----------
            // 仕入金額取得
            // -----------
            $tmpSupplySlipInfoList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlip.date AS supply_slip_date',
                'SupplySlip.total AS supply_slip_total'
            )
            ->whereBetween('SupplySlip.date', [$firstOfMonth, $endOfMonth])
            ->where('SupplySlip.active', '=', '1')
            ->get();

            // 取得してきたデータを整形する
            $supplySlipInfoList['month_total'] = 0;
            foreach ($tmpSupplySlipInfoList as $supplySlipInfo) {
                // 日付をキーに配列を入れ替える
                $supplySlipInfoList[$supplySlipInfo->supply_slip_date] = $supplySlipInfo->supply_slip_total;

                // 月の合計を計算
                $supplySlipInfoList['month_total'] += $supplySlipInfo->supply_slip_total;
            }

            // ---------
            // 売上額取得
            // ---------
            $tmpSaleSlipInfoList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.date AS sale_slip_date',
                'SaleSlip.total AS sale_slip_total'
            )
            ->whereBetween('SaleSlip.date', [$firstOfMonth, $endOfMonth])
            ->where('SaleSlip.active', '=', '1')
            ->get();

            // 取得してきたデータを整形する
            $saleSlipInfoList['month_total'] = 0;
            foreach ($tmpSaleSlipInfoList as $saleSlipInfo) {
                // 日付をキーに配列を入れ替える
                $saleSlipInfoList[$saleSlipInfo->sale_slip_date] = $saleSlipInfo->sale_slip_total;

                // 月の合計を計算
                $saleSlipInfoList['month_total'] += $saleSlipInfo->sale_slip_total;
            }

            // ---------
            // 入金額取得
            // ---------
            $tmpDepositsInfoList = DB::table('deposits AS Deposit')
            ->select(
                'Deposit.date AS Deposit_date',
                'Deposit.amount AS Deposit_total'
            )
            ->whereBetween('Deposit.date', [$firstOfMonth, $endOfMonth])
            ->where('Deposit.active', '=', '1')
            ->get();

            // 取得してきたデータを整形する
            $depositsInfoList['month_total'] = 0;
            foreach ($tmpDepositsInfoList as $depositsInfo) {
                // 日付をキーに配列を入れ替える
                $depositsInfoList[$depositsInfo->Deposit_date] = $depositsInfo->Deposit_total;

                // 月の合計を計算
                $depositsInfoList['month_total'] += $depositsInfo->Deposit_total;
            }

            // ---------
            // 出金額取得
            // ---------
            $tmpWithdrawalsInfoList = DB::table('withdrawals AS Withdrawal')
            ->select(
                'Withdrawal.date AS withdrawal_date',
                'Withdrawal.amount AS withdrawal_total'
            )
            ->whereBetween('Withdrawal.date', [$firstOfMonth, $endOfMonth])
            ->where('Withdrawal.active', '=', '1')
            ->get();

            // 取得してきたデータを整形する
            $withdrawalsInfoList['month_total'] = 0;
            foreach ($tmpWithdrawalsInfoList as $withdrawalsInfo) {
                // 日付をキーに配列を入れ替える
                $withdrawalsInfoList[$withdrawalsInfo->withdrawal_date] = $withdrawalsInfo->withdrawal_total;

                // 月の合計を計算
                $withdrawalsInfoList['month_total'] += $withdrawalsInfo->withdrawal_total;
            }

            // -------------------
            // 本日のお知らせ情報取得
            // -------------------
            // 情報格納用配列初期化
            $todayAnnouncement = array();
            $tmpTodayAnnouncementList = DB::table('sale_slip_details AS SaleSlipDetail')
            ->select(
                'SaleSlipDetail.unit_num AS unit_num',
                'SaleSlipDetail.supply_unit_num AS supply_unit_num'
            )
            ->join('sale_slips AS SaleSlip', function ($join) {
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id');
            })
            ->where([
                ['SaleSlip.date', '=', $today],
                ['SaleSlipDetail.active', '=', '1']
            ])
            ->get();

            // 仕入伝票が未設定の売上伝票数をカウント
            foreach ($tmpTodayAnnouncementList as $todayAnnouncementDatas) {
                if ($todayAnnouncementDatas->unit_num != $todayAnnouncementDatas->supply_unit_num) {
                    if (!isset($todayAnnouncement['notSetSlipCnt'])) $todayAnnouncement['notSetSlipCnt'] = 0; // カウンター
                    $todayAnnouncement['notSetSlipCnt']++;
                }
            }

        } catch (\Exception $e) {

            dd($e);

            return view('home')->with([
                'errorMessage' => $e
            ]);

        }

        return view('home')->with([
            'supplySlipInfoList'  => $supplySlipInfoList,
            'saleSlipInfoList'    => $saleSlipInfoList,
            'withdrawalsInfoList' => $withdrawalsInfoList,
            'depositsInfoList'    => $depositsInfoList,
            'todayAnnouncement'   => $todayAnnouncement,
        ]);
    }

    public static function authOwnerCheck() {

        // ユーザ情報取得
        $user_info = \Auth::user();

        if ($user_info['role'] >= 5) {
            return true;
        } else {
            return false;
        }

    }

    public static function authClerkCheck() {

        // ユーザ情報取得
        $user_info = \Auth::user();

        if ($user_info['role'] >= 3) {
            return true;
        } else {
            return false;
        }

    }
}

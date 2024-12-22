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
                if (!isset($supplySlipInfoList[$supplySlipInfo->supply_slip_date])) {
                    $supplySlipInfoList[$supplySlipInfo->supply_slip_date] = 0;
                }
                $supplySlipInfoList[$supplySlipInfo->supply_slip_date] += $supplySlipInfo->supply_slip_total;

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
                if (!isset($saleSlipInfoList[$saleSlipInfo->sale_slip_date])) {
                    $saleSlipInfoList[$saleSlipInfo->sale_slip_date] = 0;
                }
                $saleSlipInfoList[$saleSlipInfo->sale_slip_date] += $saleSlipInfo->sale_slip_total;

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
                if (!isset($depositsInfoList[$depositsInfo->Deposit_date])) {
                    $depositsInfoList[$depositsInfo->Deposit_date] = 0;
                }
                $depositsInfoList[$depositsInfo->Deposit_date] += $depositsInfo->Deposit_total;

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
                if (!isset($withdrawalsInfoList[$withdrawalsInfo->withdrawal_date])) {
                    $withdrawalsInfoList[$withdrawalsInfo->withdrawal_date] = 0;
                }
                $withdrawalsInfoList[$withdrawalsInfo->withdrawal_date] += $withdrawalsInfo->withdrawal_total;

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function top()
    {
        $supply_index_action = './SupplySlipIndex';
        $sale_index_action = './SaleSlipIndex';

        try {

            // 本日を含めて過去3日のデータを集計する。
            $today = date("Y-m-d");
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $the_day_before_yesterday = date("Y-m-d", strtotime("-2 day"));

            // 実績のデータを入れる配列
            $achievementsArray= [];

            // 実績の日付を入れる
            $achievementsArray = [
                $today => [
                    'date' => date("m月d日"),
                    'supply' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                    'sale' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                ],
                $yesterday => [
                    'date' => date("m月d日", strtotime("-1 day")),
                    'supply' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                    'sale' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                ],
                $the_day_before_yesterday => [
                    'date' => date("m月d日", strtotime("-2 day")),
                    'supply' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                    'sale' => [
                        'count' => 0,
                        'notax_amount' => 0,
                    ],
                ]
            ];

            // -----------
            // 仕入金額取得
            // -----------
            $tmpSupplySlipInfoList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlip.date AS supply_slip_date'
            )
            ->selectRaw('count(SupplySlip.id) AS count')
            ->selectRaw('SUM(COALESCE(SupplySlip.notax_sub_total,0)) AS notax_amount')
            ->whereBetween('SupplySlip.date', [$the_day_before_yesterday, $today])
            ->where('SupplySlip.active', '=', '1')
            ->groupBy('SupplySlip.date')
            ->get();


            foreach ($tmpSupplySlipInfoList as $supplySlipInfo) {

                // 件数と合計額を挿入する
                $achievementsArray[$supplySlipInfo->supply_slip_date]['supply']['count'] = $supplySlipInfo->count;
                $achievementsArray[$supplySlipInfo->supply_slip_date]['supply']['notax_amount'] = $supplySlipInfo->notax_amount;
            }

            // ---------
            // 売上額取得
            // ---------
            $tmpSaleSlipInfoList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.date AS sale_slip_date'
            )
            ->selectRaw('count(SaleSlip.id) AS count')
            ->selectRaw('SUM(COALESCE(SaleSlip.notax_sub_total,0)) AS notax_amount')
            ->whereBetween('SaleSlip.date', [$the_day_before_yesterday, $today])
            ->where('SaleSlip.active', '=', '1')
            ->groupBy('SaleSlip.date')
            ->get();

            // 取得してきたデータを整形する
            foreach ($tmpSaleSlipInfoList as $saleSlipInfo) {

                // 件数と合計額を挿入する
                $achievementsArray[$saleSlipInfo->sale_slip_date]['sale']['count'] = $saleSlipInfo->count;
                $achievementsArray[$saleSlipInfo->sale_slip_date]['sale']['notax_amount'] = $saleSlipInfo->notax_amount;
            }


        } catch (\Exception $e) {

            return view('home')->with([
                'errorMessage' => $e
            ]);

        }

        return view('top')->with([
            'supply_index_action' => $supply_index_action,
            'sale_index_action'   => $sale_index_action,
            'achievementsArray'   => $achievementsArray,
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

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class PaymentController extends Controller
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
     * 入金消込照合一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // 抽出対象の締め日 15:15日 88:都度 99:末日
        $condition_closing_date = 99;

        // 集計日付 デフォルトは先月の月初から月末
        $condition_date_from = null;
        $condition_date_to   = null;

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST") { // ページング処理

            $condition_closing_date  = $request->session()->get('condition_closing_date');
            $condition_date_from     = $request->session()->get('condition_date_from');
            $condition_date_to       = $request->session()->get('condition_date_to');
            $condition_staff_code    = $request->session()->get('condition_staff_code');
            $condition_staff_id      = $request->session()->get('condition_staff_id');
            $condition_staff_text    = $request->session()->get('condition_staff_text');
            $condition_search_text   = $request->session()->get('condition_search_text');
            $condition_company_code  = $request->session()->get('condition_company_code');
            $condition_company_id    = $request->session()->get('condition_company_id');
            $condition_company_text  = $request->session()->get('condition_company_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                if (isset($request->data)) {
                    $req_data = $request->data;
                }

                $condition_closing_date  = isset($req_data['closing_date']) ? $req_data['closing_date'] : 99;

                 // 日付の設定
                $condition_date_from     = isset($req_data['date_from']) ? $req_data['date_from'] : NULL;
                $condition_date_to       = isset($req_data['date_to']) ? $req_data['date_to'] : NULL;
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $condition_staff_code    = isset($req_data['staff_code']) ? $req_data['staff_code'] : NULL;
                $condition_staff_id      = isset($req_data['staff_id']) ? $req_data['staff_id'] : NULL;
                $condition_staff_text    = isset($req_data['staff_text']) ? $req_data['staff_text'] : NULL;
                $condition_search_text    = isset($req_data['search_text']) ? $req_data['search_text'] : NULL;
                $condition_company_code    = isset($req_data['company_code']) ? $req_data['company_code'] : NULL;
                $condition_company_id      = isset($req_data['company_id']) ? $req_data['company_id'] : NULL;
                $condition_company_text    = isset($req_data['company_text']) ? $req_data['company_text'] : NULL;

                $request->session()->put('condition_closing_date', $condition_closing_date);
                $request->session()->put('condition_date_from', $condition_date_from);
                $request->session()->put('condition_date_to', $condition_date_to);
                $request->session()->put('condition_staff_code', $condition_staff_code);
                $request->session()->put('condition_staff_id', $condition_staff_id);
                $request->session()->put('condition_staff_text', $condition_staff_text);
                $request->session()->put('condition_search_text', $condition_search_text);
                $request->session()->put('condition_company_code', $condition_company_code);
                $request->session()->put('condition_company_id', $condition_company_id);
                $request->session()->put('condition_company_text', $condition_company_text);
            } else { // リセットボタンが押された時の処理

                // 締め日
                $condition_closing_date  = 99;
                // 集計日付
                $condition_date_from     = null;
                $condition_date_to       = null;

                $condition_staff_code    = null;
                $condition_staff_id      = null;
                $condition_staff_text    = null;
                $condition_search_text   = null;
                $condition_company_code  = null;
                $condition_company_id    = null;
                $condition_company_text  = null;

                $request->session()->forget('condition_closing_date');
                $request->session()->forget('condition_date_from');
                $request->session()->forget('condition_date_to');
                $request->session()->forget('condition_staff_code');
                $request->session()->forget('condition_staff_id');
                $request->session()->forget('condition_staff_text');
                $request->session()->forget('condition_search_text');
                $request->session()->forget('condition_company_code');
                $request->session()->forget('condition_company_id');
                $request->session()->forget('condition_company_text');
            }
        }

        if(empty($condition_closing_date)) {
            $condition_closing_date = 99;
            $request->session()->put('condition_closing_date', $condition_closing_date);
        }

        // 集計日付が入っていない場合、先月の月初と月末の日付を入れる
        if(
            empty($condition_date_from) ||
            empty($condition_date_to)
        ) {
            // 先月の月初
            $condition_date_from = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            // 先月の月末
            $condition_date_to = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            $request->session()->put('condition_date_from', $condition_date_from);
            $request->session()->put('condition_date_to', $condition_date_to);
        }

        // 締め日の配列
        $closing_date_list = [
            99 => '末日',
            15 => '15日',
            88 => '都度',
        ];

        // CSV出力種別を設定
        $csv_type_arr = [
            0 => '売掛金一覧',
            1 => '入金一覧',
        ];

        try {

            //---------------------
            // 指定期間の売掛金と入金一覧を取得
            //---------------------

            // サブクエリで企業ごとの入金合計を集計
            $subQuery = DB::table('accounts_receivable_payments')
                ->select('sale_company_id', DB::raw('SUM(amount) AS payment_amount_sum'))
                ->whereBetween('payment_date', [$condition_date_from, $condition_date_to])
                ->groupBy('sale_company_id');


            $paymentReconciliationList = DB::table('sale_slips AS SaleSlip')
                ->select(
                    'SaleCompany.code AS sale_company_code',
                    'SaleCompany.name AS sale_company_name',
                    'SaleCompany.invoice_display_flg AS sale_invoice_display_flg',
                    'SaleCompany.invoice_display_name AS sale_company_invoice_display_name',
                    DB::raw('SUM(SaleSlip.notax_sub_total_8) AS notax_sub_total_8_sum'),
                    DB::raw('SUM(SaleSlip.notax_sub_total_10) AS notax_sub_total_10_sum'),
                    DB::raw('IFNULL(PaymentSummary.payment_amount_sum, 0) AS accounts_receivable_payment_amount')
                )
                ->join('sale_companies AS SaleCompany', 'SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                ->leftJoin(DB::raw("({$subQuery->toSql()}) AS PaymentSummary"), function ($join) use ($subQuery) {
                    $join->on('PaymentSummary.sale_company_id', '=', 'SaleCompany.id');
                })
                ->mergeBindings($subQuery) // 重要：バインディングをマージ
                ->where([
                    ['SaleSlip.active', '=', '1'],
                    ['SaleCompany.active', '=', '1'],
                    ['SaleCompany.closing_date', '=', $condition_closing_date],
                ])
                ->when(!empty($condition_staff_id), function ($query) use ($condition_staff_id) {
                    return $query->where('SaleCompany.staff_id', '=', $condition_staff_id);
                })
                ->when(!empty($condition_search_text), function ($query) use ($condition_search_text) {
                    return $query->where('SaleCompany.name', 'like', '%' . $condition_search_text . '%');
                })
                ->when(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                    return $query->where('SaleSlip.sale_company_id', '=', $condition_company_id);
                })
                ->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to])
                ->orderBy('SaleSlip.sale_company_id', 'desc')
                ->groupBy('SaleSlip.sale_company_id')
                ->paginate(100);


            // コレクションとして加工
            $paymentReconciliationListCollection = $paymentReconciliationList->getCollection()->map(function ($item) {
                $taxSubTotal8 = $item->notax_sub_total_8_sum * 1.08;
                $taxSubTotal10 = $item->notax_sub_total_10_sum * 1.1;
                $taxTotal = floor($taxSubTotal8 + $taxSubTotal10);

                // `null` の場合は 0 にする
                $accountsReceivablePaymentAmount = $item->accounts_receivable_payment_amount ?? 0;

                return (object) [
                    'company_name' => $item->sale_invoice_display_flg == 1
                        ? $item->sale_company_invoice_display_name
                        : $item->sale_company_name,
                    'tax_total' => $taxTotal,
                    'accounts_receivable_payment_amount' => $accountsReceivablePaymentAmount,
                    'difference' => $taxTotal - $accountsReceivablePaymentAmount,
                ];
            });

            // ページネーションを維持しつつ、更新したコレクションをセット
            $paymentReconciliationList = $paymentReconciliationList->setCollection($paymentReconciliationListCollection);


        } catch (\Exception $e) {

            dd($e);
        }

        return view('payments.index')->with([
            "condition_closing_date"     => $condition_closing_date,
            "condition_date_from"        => $condition_date_from,
            "condition_date_to"          => $condition_date_to,
            "condition_staff_code"       => $condition_staff_code,
            "condition_staff_id"         => $condition_staff_id,
            "condition_staff_text"       => $condition_staff_text,
            "condition_search_text"      => $condition_search_text,
            "condition_company_code"     => $condition_company_code,
            "condition_company_id"       => $condition_company_id,
            "condition_company_text"     => $condition_company_text,
            "closing_date_list"          => $closing_date_list,
            "csv_type_arr"               => $csv_type_arr,
            "paymentReconciliationList"  => $paymentReconciliationList,
        ]);
    }

    /**
     * 入金消込csvダウンロード処理
     *
     * @param Request $request
     * @return void
     */
    public function csvDownload(Request $request)
    {
        // CSVのダウンロードタイプを取得
        $type = $request->query('type');

        // セッションにある検索条件を取得する
        $condition_closing_date  = $request->session()->get('condition_closing_date');
        $condition_date_from     = $request->session()->get('condition_date_from');
        $condition_date_to       = $request->session()->get('condition_date_to');
        $condition_staff_id      = $request->session()->get('condition_staff_id');
        $condition_search_text   = $request->session()->get('condition_search_text');
        $condition_company_id    = $request->session()->get('condition_company_id');

        if ($type == 0) {
            // 売掛金一覧取得
            $paymentReconciliationList = DB::table('sale_slips AS SaleSlip')
                ->select(
                    'SaleCompany.code                  AS sale_company_code',
                    'SaleCompany.name                  AS sale_company_name',
                    'SaleCompany.invoice_display_flg   AS sale_invoice_display_flg',
                    'SaleCompany.invoice_display_name  AS sale_company_invoice_display_name',
                    'SaleCompany.bank_account_name     AS bank_account_name',
                    'Staff.name_sei                    AS staff_name_sei',
                    'Staff.name_mei                    AS staff_name_mei',
                )
                ->selectRaw('SUM(SaleSlip.notax_sub_total_8) AS notax_sub_total_8_sum')
                ->selectRaw('SUM(SaleSlip.notax_sub_total_10) AS notax_sub_total_10_sum')
                ->join('sale_companies AS SaleCompany', function ($join) {
                    $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
                })
                ->join('staffs AS Staff', function ($join) {
                    $join->on('Staff.id', '=', 'SaleCompany.staff_id');
                })
                ->where([
                    ['SaleSlip.active', '=', '1'],
                    ['SaleCompany.active', '=', '1'],
                    ['SaleCompany.closing_date', '=', $condition_closing_date],
                ])
                ->when(!empty($condition_staff_id), function ($query) use ($condition_staff_id) {
                    return $query->where('SaleCompany.staff_id', '=', $condition_staff_id);
                })
                ->when(!empty($condition_search_text), function ($query) use ($condition_search_text) {
                    return $query->where('SaleCompany.name', 'like', '%' . $condition_search_text . '%');
                })
                ->when(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                    return $query->where('SaleSlip.sale_company_id', '=', $condition_company_id);
                })
                ->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to])
                ->orderBy('SaleSlip.sale_company_id', 'desc')
                ->groupBy('SaleSlip.sale_company_id')
                ->get();

            // コレクション整形
            $paymentReconciliationListCollection = $paymentReconciliationList->map(function ($item) use ($condition_date_from, $condition_date_to) {
                $taxSubTotal8 = $item->notax_sub_total_8_sum * 1.08;
                $taxSubTotal10 = $item->notax_sub_total_10_sum * 1.1;
                $taxTotal = floor($taxSubTotal8 + $taxSubTotal10);

                return [
                    '対象期間_開始日'      => $condition_date_from,
                    '対象期間_終了日'      => $condition_date_to,
                    '得意先コード'         => $item->sale_company_code,
                    '得意先名'             => $item->sale_invoice_display_flg == 1
                        ? $item->sale_company_invoice_display_name
                        : $item->sale_company_name,
                    '振込口座名'           => $item->bank_account_name,
                    '税込合計金額'         => $taxTotal,
                    '担当者'               => $item->staff_name_sei . '' . $item->staff_name_mei,
                ];
            });

            // CSV出力処理
            $csvFilename = 'payment_reconciliation_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type'        => 'text/csv; charset=SJIS-win',
                'Content-Disposition' => "attachment; filename=\"$csvFilename\"",
            ];

            $callback = function () use ($paymentReconciliationListCollection) {
                // SJIS変換のためにPHPのストリームを使う
                $stream = fopen('php://output', 'w');

                // BOMを出力（ExcelがUTF-8と認識するため）
                fwrite($stream, "\xEF\xBB\xBF");

                // 1行目（タイトル行）
                fputcsv($stream, ['売掛金一覧CSV（対象期間付き）'], ',', '"');

                // 2行目（ヘッダー）
                if ($paymentReconciliationListCollection->isNotEmpty()) {
                    fputcsv($stream, array_keys($paymentReconciliationListCollection->first()), ',', '"');
                }

                // データ行（ここ重要：SJISに変換しない！！）
                foreach ($paymentReconciliationListCollection as $row) {
                    fputcsv($stream, $row); // UTF-8のまま出力
                }

                fclose($stream);
            };

            return response()->stream($callback, 200, $headers);
        }

        // 無効なtypeの場合
        return redirect()->back()->with('error', '無効なCSVタイプです');
    }

    public function input()
    {
        return view('payments.input');
    }

    public function confirm(Request $request)
    {
        $payment_date = $request->input('payment_date');
        $payments = collect($request->input('payments'))->filter(function ($p) {
            return !empty($p['company_id']) && !empty($p['amount']);
        })->values()->toArray();

        if (empty($payments)) {
            return redirect()->route('payments.input')->with('error', '入力情報が不足しています');
        }

        Session::put('payments', $payments);
        Session::put('payment_date', $payment_date);

        return view('payments.confirm', compact('payments', 'payment_date'));
    }

    public function store(Request $request)
    {
        $payments = Session::get('payments', []);
        $payment_date = Session::get('payment_date');

        if (empty($payments)) {
            return redirect()->route('payments.input')->with('error', 'セッションが無効です');
        }

        try {
            DB::beginTransaction();

            foreach ($payments as $payment) {
                DB::table('accounts_receivable_payments')->insert([
                    'sale_company_id' => $payment['company_id'],
                    'payment_date' => $payment_date,
                    'amount' => $payment['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            Session::forget('payments');
            Session::forget('payment_date');

            return view('payments.complete');
        } catch (\Exception $e) {
            DB::rollBack();
            //\Log::error('入金登録エラー: ' . $e->getMessage());
            return redirect()->route('payments.input')->with('error', '登録処理に失敗しました。');
        }
    }

    public function list(Request $request)
    {
        // 締め日リスト、CSV種別
        $closing_date_list = [
            99 => '末日',
            15 => '15日',
            88 => '都度',
        ];
        $csv_type_arr = [
            0 => '売掛金一覧',
            1 => '入金一覧',
        ];

        // セッション or POSTから検索条件を取得
        if ($request->isMethod('post')) {
            $data = $request->input('data');

            if ($request->has('search-btn')) {
                $condition_closing_date = $data['closing_date'] ?? 99;
                $condition_date_from = $data['date_from'] ?? null;
                $condition_date_to = $data['date_to'] ?? null;
                if ($condition_date_from && !$condition_date_to) $condition_date_to = $condition_date_from;
                if (!$condition_date_from && $condition_date_to) $condition_date_from = $condition_date_to;

                $condition_staff_code = $data['staff_code'] ?? null;
                $condition_staff_id = $data['staff_id'] ?? null;
                $condition_staff_text = $data['staff_text'] ?? null;
                $condition_search_text = $data['search_text'] ?? null;
                $condition_company_code = $data['company_code'] ?? null;
                $condition_company_id = $data['company_id'] ?? null;
                $condition_company_text = $data['company_text'] ?? null;

                // セッションに保存
                session([
                    'condition_closing_date' => $condition_closing_date,
                    'condition_date_from' => $condition_date_from,
                    'condition_date_to' => $condition_date_to,
                    'condition_staff_code' => $condition_staff_code,
                    'condition_staff_id' => $condition_staff_id,
                    'condition_staff_text' => $condition_staff_text,
                    'condition_search_text' => $condition_search_text,
                    'condition_company_code' => $condition_company_code,
                    'condition_company_id' => $condition_company_id,
                    'condition_company_text' => $condition_company_text,
                ]);
            } else { // リセット
                session()->forget([
                    'condition_closing_date',
                    'condition_date_from',
                    'condition_date_to',
                    'condition_staff_code',
                    'condition_staff_id',
                    'condition_staff_text',
                    'condition_search_text',
                    'condition_company_code',
                    'condition_company_id',
                    'condition_company_text'
                ]);
            }
        }

        // セッションから取得（POSTで設定されたか、リセットされたか）
        $condition_closing_date = session('condition_closing_date', 99);
        $condition_date_from = session('condition_date_from', Carbon::now()->subMonth()->startOfMonth()->toDateString());
        $condition_date_to = session('condition_date_to', Carbon::now()->subMonth()->endOfMonth()->toDateString());
        $condition_staff_code = session('condition_staff_code');
        $condition_staff_id = session('condition_staff_id');
        $condition_staff_text = session('condition_staff_text');
        $condition_search_text = session('condition_search_text');
        $condition_company_code = session('condition_company_code');
        $condition_company_id = session('condition_company_id');
        $condition_company_text = session('condition_company_text');

        // 入金一覧取得（絞り込み付き）
        $payments = DB::table('accounts_receivable_payments AS p')
            ->join('sale_companies AS c', 'p.sale_company_id', '=', 'c.id')
            ->select('p.id', 'p.payment_date', 'p.amount', 'c.name AS company_name')
            ->when($condition_closing_date, function ($q) use ($condition_closing_date) {
                return $q->where('c.closing_date', $condition_closing_date);
            })
            ->when($condition_staff_id, function ($q) use ($condition_staff_id) {
                return $q->where('c.staff_id', $condition_staff_id);
            })
            ->when($condition_search_text, function ($q) use ($condition_search_text) {
                return $q->where('c.name', 'like', '%' . $condition_search_text . '%');
            })
            ->when($condition_company_id, function ($q) use ($condition_company_id) {
                return $q->where('c.id', $condition_company_id);
            })
            ->whereBetween('p.payment_date', [$condition_date_from, $condition_date_to])
            ->orderBy('p.payment_date', 'desc')
            ->paginate(100);

        return view('payments.list', compact(
            'payments',
            'condition_closing_date',
            'condition_date_from',
            'condition_date_to',
            'condition_staff_code',
            'condition_staff_id',
            'condition_staff_text',
            'condition_search_text',
            'condition_company_code',
            'condition_company_id',
            'condition_company_text',
            'closing_date_list',
            'csv_type_arr'
        ));
    }

    public function edit($id)
    {
        $payment = DB::table('accounts_receivable_payments AS p')
            ->join('sale_companies AS c', 'p.sale_company_id', '=', 'c.id')
            ->where('p.id', $id)
            ->select('p.*', 'c.name AS company_name')
            ->first();

        if (!$payment) {
            return redirect()->route('payments.list')->with('error', 'データが見つかりませんでした。');
        }

        return view('payments.edit', compact('payment'));
    }

    public function editConfirm($id, Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payment = DB::table('accounts_receivable_payments AS p')
            ->join('sale_companies AS c', 'p.sale_company_id', '=', 'c.id')
            ->where('p.id', $id)
            ->select('c.name AS company_name')
            ->first();

        return view('payments.edit_confirm', [
            'payment_id' => $id,
            'company_name' => $payment->company_name,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::table('accounts_receivable_payments')
            ->where('id', $id)
            ->update([
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'updated_at' => now(),
            ]);

        return redirect()->route('payments.list')->with('success', '入金情報を更新しました。');
    }

}

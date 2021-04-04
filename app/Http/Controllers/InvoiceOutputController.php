<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use Carbon\Carbon;

class InvoiceOutputController extends Controller
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
     * 請求書出力画面
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
            $search_action = '../InvoiceOutputIndex';
        } else {
            $search_action = './InvoiceOutputIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type         = $request->session()->get('invoiceOutput_condition_date_from');
            $condition_date_from         = $request->session()->get('invoiceOutput_condition_date_from');
            $condition_date_to           = $request->session()->get('invoiceOutput_condition_date_to');
            $condition_company_code      = $request->session()->get('invoiceOutput_condition_company_code');
            $condition_company_id        = $request->session()->get('invoiceOutput_condition_company_id');
            $condition_company_text      = $request->session()->get('invoiceOutput_condition_company_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type     = $request->data['InvoiceOutput']['date_type'];
                $condition_company_code  = $request->data['InvoiceOutput']['invoiceOutput_company_code'];
                $condition_company_id    = $request->data['InvoiceOutput']['invoiceOutput_company_id'];
                $condition_company_text  = $request->data['InvoiceOutput']['invoiceOutput_company_text'];

                // 日付の設定
                $condition_date_from = $request->data['InvoiceOutput']['invoiceOutput_date_from'];
                $condition_date_to   = $request->data['InvoiceOutput']['invoiceOutput_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('invoiceOutput_condition_date_type', $condition_date_type);
                $request->session()->put('invoiceOutput_condition_date_from', $condition_date_from);
                $request->session()->put('invoiceOutput_condition_date_to', $condition_date_to);
                $request->session()->put('invoiceOutput_condition_company_code', $condition_company_code);
                $request->session()->put('invoiceOutput_condition_company_id', $condition_company_id);
                $request->session()->put('invoiceOutput_condition_company_text', $condition_company_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_type         = null;
                $condition_date_from         = null;
                $condition_date_to           = null;
                $condition_company_code      = null;
                $condition_company_id        = null;
                $condition_company_text      = null;
                $request->session()->forget('invoceOutput_condition_date_type');
                $request->session()->forget('invoceOutput_condition_date_from');
                $request->session()->forget('invoceOutput_condition_date_to');
                $request->session()->forget('invoceOutput_condition_company_code');
                $request->session()->forget('invoceOutput_condition_company_id');
                $request->session()->forget('invoceOutput_condition_company_text');

            }
        }

        try {

            // 入金一覧を取得
            $depositList = DB::table('deposits AS Deposit')
            ->select(
                'Deposit.id                AS deposit_id',
                'Deposit.date              AS deposit_date',
                'Deposit.sale_from_date    AS sale_from_date',
                'Deposit.sale_to_date      AS sale_to_date',
                'Deposit.amount            AS amount',
                'SaleCompany.name          AS sale_company_name'
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
            ->orderBy('Deposit.date', 'desc')->paginate(20);

        } catch (\Exception $e) {

            dd($e);

            return view('Deposit.index')->with([
                'errorMessage' => $e
            ]);
        }

        // 対象日付のチェック
        $check_str_slip_date = "";
        $check_str_invoiceOutput_date = "";
        if($condition_date_type == 2) $check_str_invoiceOutput_date = "checked";
        else  $check_str_slip_date = "checked";

        return view('InvoiceOutput.index')->with([
            "search_action"                => $search_action,
            "check_str_slip_date"          => $check_str_slip_date,
            "check_str_invoiceOutput_date" => $check_str_invoiceOutput_date,
            "condition_date_from"          => $condition_date_from,
            "condition_date_to"            => $condition_date_to,
            "condition_company_code"       => $condition_company_code,
            "condition_company_id"         => $condition_company_id,
            "condition_company_text"       => $condition_company_text,
            "depositList"                  => $depositList
        ]);
    }

    public function output(Request $request)
    {
        // リクエストパラメータ取得
        $reqData = $request->data['InvoiceOutput'];

        // 請求情報を取得
        $depositList = DB::table('deposits AS Deposit')
        ->select(
            'DepositWithdrawalDetail.notax_sub_total_8  AS notax_sub_total_8',
            'DepositWithdrawalDetail.notax_sub_total_10 AS notax_sub_total_10',
            'DepositWithdrawalDetail.delivery_price     AS delivery_price',
            'DepositWithdrawalDetail.adjust_price       AS adjust_price',
            'Deposit.remarks                            AS remarks',
            'SaleCompany.name                           AS company_name',
            'SaleCompany.postal_code                    AS company_postal_code',
            'SaleCompany.address                        AS company_address'
        )
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
        })
        ->join('deposit_withdrawal_details AS DepositWithdrawalDetail', function ($join) {
            $join->on('DepositWithdrawalDetail.deposit_withdrawal_id', '=', 'Deposit.id');
        })
        ->where('Deposit.active', '=', '1')
        ->get();
// error_log(print_r($depositList, true), '3', '/home/gfproject/mizucho.com/public_html/slmn-project/storage/logs/error.log');

        // ------------------------
        // 8%, 10%の計算をそれぞれする
        // ------------------------
        $calcDepositList = array(
            'notax_subtotal_8'    => 0,     // 8%税抜金額
            'tax_8'               => 0,     // 8%消費税
            'subtotal_8'          => 0,     // 8%税込金額
            'notax_subtotal_10'   => 0,     // 10%税抜金額
            'tax_10'              => 0,     // 10%消費税
            'subtotal_10'         => 0,     // 10%税込金額
            'total'               => 0,     // 総合計金額
            'delivery_price'      => 0,     // 配送額
            'adjust_price'        => 0,     // 調整額
            'company_name'        => '',    // 企業名
            'company_postal_code' => '',    // 郵便番号
            'company_address'     => ''     // 住所
        );
        foreach ($depositList as $depositDatas) {

            // 企業情報格納
            if (empty($calcDepositList['company_name']) || empty($calcDepositList['company_postal_code']) || empty($calcDepositList['company_address'])) {
                $calcDepositList['company_name']    = $depositDatas->company_name;
                $calcDepositList['company_address'] = $depositDatas->company_address;
                // 郵便番号は間にハイフンを入れる
                $codeBefore = substr($depositDatas->company_postal_code, 0, 3);
                $codeAfter  = substr($depositDatas->company_postal_code, 3, 4);
                $calcDepositList['company_postal_code'] = $codeBefore . '-' . $codeAfter;
            }

            // -------
            // 金額計算
            // -------
            // 8%消費税
            $tax8 = round($depositDatas->notax_sub_total_8 * 0.08);
            // 8%税込金額
            $subtotal8 = $depositDatas->notax_sub_total_8 + $tax8;

            // 10%消費税
            $tax10 = round($depositDatas->notax_sub_total_10 * 0.1);
            // 10%税込金額
            $subtotal10 = $depositDatas->notax_sub_total_10 + $tax10;

            // データ格納
            $calcDepositList['notax_subtotal_8']  += $depositDatas->notax_sub_total_8;
            $calcDepositList['tax_8']             += $tax8;
            $calcDepositList['subtotal_8']        += $subtotal8;
            $calcDepositList['notax_subtotal_10'] += $depositDatas->notax_sub_total_10;
            $calcDepositList['tax_10']            += $tax10;
            $calcDepositList['subtotal_10']       += $subtotal10;
            $calcDepositList['total']             += $subtotal8 + $subtotal10;
            $calcDepositList['delivery_price']    += $depositDatas->delivery_price;
            $calcDepositList['adjust_price']      += $depositDatas->adjust_price;

        }
// error_log(print_r($calcDepositList, true), '3', '/home/gfproject/mizucho.com/public_html/slmn-project/storage/logs/error.log');
        $pdf = \PDF::view('pdf.pdf_tamplate', [
                'depositList' => $calcDepositList
            ])
            ->setOption('encoding', 'utf-8');
        return $pdf->inline('thisis.pdf');  //ブラウザ上で開ける
        // return $pdf->download('thisis.pdf'); //こっちにすると直接ダウンロード
    }

}

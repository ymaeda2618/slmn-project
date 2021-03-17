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
            "withdrawalList"               => $withdrawalList
        ]);
    }

}

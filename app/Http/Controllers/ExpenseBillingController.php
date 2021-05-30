<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Staff;
use App\ExpenseItem;
use App\ExpenseBilling;
use Carbon\Carbon;
use Exception;

class ExpenseBillingController extends Controller
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
     * 経費一覧
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
            $search_action = '../ExpenseBillingIndex';
        } else {
            $search_action = './ExpenseBillingIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type      = $request->session()->get('expense_billing_condition_date_type');
            $condition_date_from      = $request->session()->get('expense_billing_condition_date_from');
            $condition_date_to        = $request->session()->get('expense_billing_condition_date_to');
            $condition_company_code   = $request->session()->get('expense_billing_condition_company_code');
            $condition_company_id     = $request->session()->get('expense_billing_condition_company_id');
            $condition_company_text   = $request->session()->get('expense_billing_condition_company_text');
            $condition_staff_code     = $request->session()->get('expense_billing_condition_staff_code');
            $condition_staff_id       = $request->session()->get('expense_billing_condition_staff_id');
            $condition_staff_text     = $request->session()->get('expense_billing_condition_staff_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type    = $request->data['ExpenseBilling']['date_type'];
                $condition_company_code = $request->data['ExpenseBilling']['expense_billingcompany_code'];
                $condition_company_id   = $request->data['ExpenseBilling']['expense_billingcompany_id'];
                $condition_company_text = $request->data['ExpenseBilling']['expense_billingcompany_text'];

                // 日付の設定
                $condition_date_from = $request->data['ExpenseBilling']['expense_billingdate_from'];
                $condition_date_to   = $request->data['ExpenseBilling']['expense_billingdate_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $condition_staff_code     = $request->data['ExpenseBilling']['expense_billing_staff_code'];
                $condition_staff_id       = $request->data['ExpenseBilling']['expense_billing_staff_id'];
                $condition_staff_text     = $request->data['ExpenseBilling']['expense_billing_staff_text'];

                $request->session()->put('expense_billing_condition_date_type', $condition_date_type);
                $request->session()->put('expense_billing_condition_date_from', $condition_date_from);
                $request->session()->put('expense_billing_condition_date_to', $condition_date_to);
                $request->session()->put('expense_billing_condition_company_code', $condition_company_code);
                $request->session()->put('expense_billing_condition_company_id', $condition_company_id);
                $request->session()->put('expense_billing_condition_company_text', $condition_company_text);
                $request->session()->put('expense_billing_condition_staff_code', $condition_staff_code);
                $request->session()->put('expense_billing_condition_staff_id', $condition_staff_id);
                $request->session()->put('expense_billing_condition_staff_text', $condition_staff_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_type      = null;
                $condition_date_from      = null;
                $condition_date_to        = null;
                $condition_company_code   = null;
                $condition_company_id     = null;
                $condition_company_text   = null;
                $condition_staff_code     = null;
                $condition_staff_id       = null;
                $condition_staff_text     = null;
                $request->session()->forget('expense_billing_condition_id');
                $request->session()->forget('expense_billing_condition_date_type');
                $request->session()->forget('expense_billing_condition_date_from');
                $request->session()->forget('expense_billing_condition_date_to');
                $request->session()->forget('expense_billing_condition_sale_date_from');
                $request->session()->forget('expense_billing_condition_sale_date_to');
                $request->session()->forget('expense_billing_condition_company_code');
                $request->session()->forget('expense_billing_condition_company_id');
                $request->session()->forget('expense_billing_condition_company_text');
                $request->session()->forget('expense_billing_condition_staff_code');
                $request->session()->forget('expense_billing_condition_staff_id');
                $request->session()->forget('expense_billing_condition_staff_text');

            }
        }

        try {

            // 経費一覧を取得
            $depositList = DB::table('expense_billings AS ExpenseBilling')
            ->select(
                'ExpenseBilling.id                  AS id',
                'ExpenseBilling.date                AS date',
                'ExpenseBilling.due_date            AS due_date',
                'SupplyCompany.name                 AS supply_company_name',
                'ExpenseItem.name                   AS expense_item_name',
                'ExpenseBilling.name                AS name',
                'Staff.name                         AS staff_company_name',
                'ExpenseBilling.price               AS price',
            )
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'ExpenseBilling.supply_company_id');
            })
            ->join('expense_items AS ExpenseItem', function ($join) {
                $join->on('ExpenseItem.id', '=', 'ExpenseBilling.expense_item_id');
            })
            ->leftJoin('staffs AS Staff', function ($join) {
                $join->on('Staff.id', '=', 'ExpenseBilling.staff_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('ExpenseBilling.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('ExpenseBilling.due_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('ExpenseBilling.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_staff_id), function ($query) use ($condition_staff_id) {
                return $query->where('ExpenseBilling.staff_id', '=', $condition_staff_id);
            })
            ->where('ExpenseBilling.active', '=', '1')
            ->orderBy('ExpenseBilling.date', 'desc')
            ->orderBy('ExpenseBilling.id', 'desc')
            ->paginate(20);

        } catch (\Exception $e) {

            dd($e);

            return view('ExpenseBilling.index')->with([
                'errorMessage' => $e
            ]);
        }

        // 対象日付のチェック
        $check_str_slip_date = "";
        $check_str_deposit_date = "";
        if($condition_date_type == 2) $check_str_deposit_date = "checked";
        else  $check_str_slip_date = "checked";

        return view('ExpenseBilling.index')->with([
            "search_action"            => $search_action,
            "check_str_slip_date"      => $check_str_slip_date,
            "check_str_deposit_date"   => $check_str_deposit_date,
            "condition_date_from"      => $condition_date_from,
            "condition_date_to"        => $condition_date_to,
            "condition_company_code"   => $condition_company_code,
            "condition_company_id"     => $condition_company_id,
            "condition_company_text"   => $condition_company_text,
            "condition_staff_code"     => $condition_staff_code,
            "condition_staff_id"       => $condition_staff_id,
            "condition_staff_text"     => $condition_staff_text,
            "depositList"              => $depositList
        ]);
    }

    /**
     * 経費新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // 経費役職一覧を取得
        $expenseItemList = ExpenseItem::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // スタッフ一覧を取得
        $staffList = Staff::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('ExpenseBilling.create')->with([
            "expenseItemList"  => $expenseItemList,
            "staffList"        => $staffList,
            "error_message"      => $error_message,
        ]);
    }

    /**
     * 経費登録
     *
     */
    public function registerExpenseBilling(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            //---------------
            // 保存処理を行う
            //---------------
            $ExpenseBilling = new ExpenseBilling;
            $ExpenseBilling->date              = $request->data['ExpenseBilling']['date'];
            $ExpenseBilling->due_date          = $request->data['ExpenseBilling']['due_date'];
            $ExpenseBilling->suppy_company_id  = $request->data['ExpenseBilling']['suppy_company_id'];
            $ExpenseBilling->expense_item_id   = $request->data['ExpenseBilling']['expense_item_id'];
            $ExpenseBilling->name              = $request->data['ExpenseBilling']['name'];
            $ExpenseBilling->price             = $request->data['ExpenseBilling']['price'];
            $ExpenseBilling->staff_id          = $request->data['ExpenseBilling']['staff_id'];
            $ExpenseBilling->memo              = $request->data['ExpenseBilling']['memo'];
            $ExpenseBilling->created_user_id   = $user_info_id;
            $ExpenseBilling->created           = Carbon::now();
            $ExpenseBilling->modified_user_id  = $user_info_id;
            $ExpenseBilling->modified          = Carbon::now();
            $ExpenseBilling->save();

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./ExpenseBillingIndex');
    }
}

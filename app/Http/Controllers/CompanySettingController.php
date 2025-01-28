<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\CompanySetting;
use Carbon\Carbon;
use Exception;

class CompanySettingController extends Controller
{
    var $bank_type = [
        0 => '-',
        1 => '普通',
        2 => '当座',
        3 => 'その他',
    ];

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
     * 企業情報 TOP画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        return view('CompanySetting.index')->with([
            'company_setting_data' => CompanySetting::getCompanyData(),
            'bank_type'            => $this->bank_type,
        ]);

    }

    /**
     * 企業情報 編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit()
    {

        return view('CompanySetting.edit')->with([
            'company_setting_data' => CompanySetting::getCompanyData(),
            'bank_type'            => $this->bank_type,
        ]);

    }

    /**
     * 企業情報 確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        $validatedData = $request->validate([
            'data.CompanySetting.postal_code'     => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.bank_code'       => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.branch_code'     => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.bank_account'    => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.office_tel'      => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.office_fax'      => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.shop_tel'        => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.shop_fax'        => ['nullable', 'regex:/^\d+$/'],
            'data.CompanySetting.invoice_form_id' => ['nullable', 'regex:/^\d+$/'],
        ],[
            'data.CompanySetting.postal_code.regex'     => '郵便番号は半角数字のみで入力してください。',
            'data.CompanySetting.bank_code.regex'       => '銀行コードは半角数字のみで入力してください。',
            'data.CompanySetting.branch_code.regex'     => '支店コードは半角数字のみで入力してください。',
            'data.CompanySetting.bank_account.regex'    => '口座番号は半角数字のみで入力してください。',
            'data.CompanySetting.office_tel.regex'      => '事務所TELは半角数字のみで入力してください。',
            'data.CompanySetting.office_fax.regex'      => '事務所FAXは半角数字のみで入力してください。',
            'data.CompanySetting.shop_tel.regex'        => '店舗TELは半角数字のみで入力してください。',
            'data.CompanySetting.shop_fax.regex'        => '店舗FAXは半角数字のみで入力してください。',
            'data.CompanySetting.invoice_form_id.regex' => '適格請求書発行事業者登録番号は半角数字のみで入力してください。',
        ]);

        $action_url = './CompanySettingEditComplete';

        $data = $request->all();

        return view('CompanySetting.confirm')->with([
            "action_url" => $action_url,
            "request"    => $data,
        ]);
    }

    /**
     * 企業情報 編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editComplete(Request $request)
    {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション処理
        DB::beginTransaction();

        try {

            //---------------
            // 保存処理を行う
            //---------------
            $CompanySetting                   = \App\CompanySetting::find($request->data['CompanySetting']['id']);
            $CompanySetting->name             = $request->data['CompanySetting']['name'];
            $CompanySetting->postal_code      = $request->data['CompanySetting']['postal_code'];
            $CompanySetting->address          = $request->data['CompanySetting']['address'];
            $CompanySetting->bank_code        = $request->data['CompanySetting']['bank_code'];
            $CompanySetting->bank_name        = $request->data['CompanySetting']['bank_name'];
            $CompanySetting->branch_code      = $request->data['CompanySetting']['branch_code'];
            $CompanySetting->branch_name      = $request->data['CompanySetting']['branch_name'];
            $CompanySetting->bank_type        = $request->data['CompanySetting']['bank_type'];
            $CompanySetting->bank_account     = $request->data['CompanySetting']['bank_account'];
            $CompanySetting->office_tel       = $request->data['CompanySetting']['office_tel'];
            $CompanySetting->office_fax       = $request->data['CompanySetting']['office_fax'];
            $CompanySetting->shop_tel         = $request->data['CompanySetting']['shop_tel'];
            $CompanySetting->shop_fax         = $request->data['CompanySetting']['shop_fax'];
            $CompanySetting->invoice_form_id  = $request->data['CompanySetting']['invoice_form_id'];
            $CompanySetting->modified_user_id = $user_info_id;               // 更新者ユーザーID
            $CompanySetting->modified         = Carbon::now();               // 更新時間

            $CompanySetting->save();

        } catch (\Exception $e) {

            DB::rollback();

            return view('CompanySetting.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('CompanySetting.complete');
    }

}
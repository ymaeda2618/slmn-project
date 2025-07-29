<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BankAccount;
use App\CompanySetting;

class BankAccountController extends Controller
{
    /**
     * 銀行口座の一覧を表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1社のみの前提で最初の企業設定を取得
        $company = CompanySetting::first();

        // 企業に紐づく銀行口座をすべて取得
        $bankAccounts = BankAccount::where('company_setting_id', $company->id)->get();

        return view('bankAccounts.index', compact('bankAccounts', 'company'));
    }

    /**
     * 銀行口座の新規作成フォームを表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('bankAccounts.create');
    }

    /**
     * 新しい銀行口座を保存
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 入力バリデーション
        $request->validate([
            'bank_name' => 'required|string',
            'branch_name' => 'required|string',
            'account_type' => 'required|in:1,2',
            'account_number' => 'required|digits:7',
        ]);

        // 企業設定（1社前提）を取得
        $company = CompanySetting::first();

        // 登録済み口座数が15件以上の場合は登録不可
        $count = BankAccount::where('company_setting_id', $company->id)->count();
        if ($count >= 15) {
            return redirect()->back()
                ->withErrors(['max_limit' => '登録できる口座は最大15件までです。'])
                ->withInput();
        }

        // 新規登録
        BankAccount::create([
            'company_setting_id' => $company->id,
            'bank_code' => $request->bank_code,
            'bank_name' => $request->bank_name,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
        ]);

        return redirect()->route('bank_accounts.index')->with('success', '口座を登録しました。');
    }

    /**
     * 銀行口座の編集フォームを表示
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // 該当する口座データを取得
        $account = BankAccount::findOrFail($id);

        return view('bankAccounts.edit', compact('account'));
    }

    /**
     * 銀行口座を更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 入力バリデーション
        $request->validate([
            'bank_code' => 'nullable|string',
            'bank_name' => 'required|string',
            'branch_code' => 'nullable|string',
            'branch_name' => 'required|string',
            'account_type' => 'required|in:1,2',
            'account_number' => 'required|digits:7',
        ]);

        // 該当する口座データを取得
        $account = BankAccount::findOrFail($id);

        // データ更新（company_setting_id は変更しない）
        $account->update([
            'bank_code' => $request->bank_code,
            'bank_name' => $request->bank_name,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
        ]);

        return redirect()->route('bank_accounts.index')->with('success', '更新しました');
    }

    /**
     * 銀行口座を削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // 該当口座を削除
        $account = BankAccount::findOrFail($id);
        $account->delete();

        return redirect()->route('bank_accounts.index')->with('success', '削除しました');
    }
}

@extends('layouts.app') @section('content')
<div class="container">
    <h2>入金入力フォーム（最大20社分）</h2>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('payments.confirm') }}" id="payment-form">
        {{ csrf_field() }}

        <div class="form-group">
            <label>入金日（共通）</label>
            <input type="date" name="payment_date" class="form-control" required>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>企業コード</th>
                    <th>企業名</th>
                    <th>入金額</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i
                < 20; $i++) <tr>
                    <td>
                        <input type="text" name="payments[{{ $i }}][company_code]" id="company_{{ $i }}_code" class="form-control company_code_input" tabindex="{{ $i * 2 + 1 }}">
                        <input type="hidden" name="payments[{{ $i }}][company_id]" id="company_{{ $i }}_id">
                    </td>
                    <td>
                        <input type="text" name="payments[{{ $i }}][company_name]" id="company_{{ $i }}_text" class="form-control" readonly>
                    </td>
                    <td>
                        <input type="number" name="payments[{{ $i }}][amount]" class="form-control amount_input" step="0.01" min="0" tabindex="{{ $i * 2 + 2 }}">
                    </td>
                    </tr>
                    @endfor
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">確認画面へ</button>
    </form>
</div>

@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {
            //-------------------------------------
            // autocomplete処理 売上企業ID
            //-------------------------------------
            $(".company_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../../AjaxAutoCompleteSaleCompany",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function() {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // フォーカスアウト時に企業名取得
            //-------------------------------------
            $(document).on("blur", "input", function() {
                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();

                // 全角数字 → 半角
                set_val = set_val.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(set_val);

                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/company/)) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../../AjaxSetSaleCompany",
                        type: "POST",
                        dataType: "JSON",
                        data: fd,
                        processData: false,
                        contentType: false
                    }).done(function(data) {
                        $("#" + selector_code).val(data[0]);
                        $("#" + selector_id).val(data[1]);
                        $("#" + selector_text).val(data[2]);
                    });
                }
            });

            //-------------------------------------
            // Enter押下でタブ移動
            //-------------------------------------
            $(document).on("keydown", "input", function(event) {
                if (event.keyCode === 13) {
                    var tabindex = parseInt($(this).attr("tabindex"), 10);
                    if (isNaN(tabindex)) return false;

                    tabindex += 1;
                    var $next = $('input[tabindex="' + tabindex + '"]');

                    if ($next.length) {
                        $next.focus();
                    }

                    return false;
                }
            });

            //-------------------------------------
            // バリデーションの処理
            //-------------------------------------
            $('#payment-form').on('submit', function(e) {
                let companyCodes = {};
                let hasError = false;
                let duplicates = [];
                let missingAmounts = [];

                // 全体の赤枠をリセット
                $('input.company_code_input, input.amount_input').removeClass('error-border');

                $('input.company_code_input').each(function() {
                    const code = $(this).val().trim();
                    const id = $(this).attr('id');
                    const index = id.match(/company_(\d+)_code/)[1];
                    const amountInput = $('input[name="payments[' + index + '][amount]"]');
                    const amount = amountInput.val().trim();

                    if (code === '') return;

                    // 重複チェック
                    if (companyCodes[code]) {
                        hasError = true;
                        duplicates.push({
                            code: code,
                            ids: [companyCodes[code], id]
                        });
                    } else {
                        companyCodes[code] = id;
                    }

                    // 金額未入力チェック（0以下も不可）
                    if (amount === '' || parseFloat(amount) <= 0) {
                        hasError = true;
                        missingAmounts.push({
                            codeId: id,
                            amountInput: amountInput
                        });
                    }
                });

                if (duplicates.length > 0) {
                    duplicates.forEach(function(dup) {
                        $('#' + dup.ids[0]).addClass('error-border');
                        $('#' + dup.ids[1]).addClass('error-border');
                    });

                    alert('同じ企業コードが複数行に入力されています。\n該当箇所を確認してください。');
                    e.preventDefault();
                    return;
                }

                if (missingAmounts.length > 0) {
                    missingAmounts.forEach(function(entry) {
                        $('#' + entry.codeId).addClass('error-border');
                        $(entry.amountInput).addClass('error-border');
                    });

                    alert('企業コードが入力されている行で、入金額が未入力または0円です。\n該当箇所を確認してください。');
                    e.preventDefault();
                    return;
                }
            });

            // 入力時に赤枠を削除
            $(document).on('input', 'input', function() {
                $(this).removeClass('error-border');
            });

        });
    })(jQuery);
</script>

<style>
    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }
    
    input[readonly] {
        background-color: #f9f9f9;
    }
    
    input.error-border {
        border: 2px solid red !important;
        background-color: #fff0f0;
    }
</style>

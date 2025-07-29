<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id'); // ← ここを修正
            $table->unsignedInteger('company_setting_id');
            $table->string('bank_code')->nullable();
            $table->string('bank_name');
            $table->string('branch_code')->nullable();
            $table->string('branch_name');
            $table->unsignedTinyInteger('account_type'); // 1 = 普通, 2 = 当座
            $table->string('account_number', 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
}

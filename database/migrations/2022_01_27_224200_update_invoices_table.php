<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->datetime('delivered_at')->after('issued_at')->nullable();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->datetime('approved_at')->after('issued_at')->nullable();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('issued_at', 'issue_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->dropColumn('delivered_at');
            $table->renameColumn('issue_at', 'issued_at');
        });
    }
}

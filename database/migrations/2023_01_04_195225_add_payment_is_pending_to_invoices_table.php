<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('payment_is_pending')->default(false)->after('paid');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->datetime('paid_at')->nullable()->change();
            $table->string('status')->nullable()->after('amount');
            $table->string('payment_intent_id')->nullable()->after('status');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('client_id');
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
            $table->dropColumn('payment_is_pending');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('payment_intent_id');
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });
    }
};

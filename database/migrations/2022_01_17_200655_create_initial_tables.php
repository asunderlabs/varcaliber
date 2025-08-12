<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /*

        Models

        - Organization (organizations)
        - User (users)
        - Organization User (organization_user)
        - Report (reports)
        - Report Item (report_items)
        - Report Item Category (report_item_categories)
        - Work Entry (work_entries)
        - Invoice (invoices)
        - Project (projects)
        - Payment (payments)

        */
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->after('password')->default(0);
            $table->json('preferences')->nullable()->after('is_admin');
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_id');
            $table->string('billing_contact');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->json('cached_items')->nullable();
            $table->unsignedSmallInteger('minutes')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });

        Schema::create('report_items', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedSmallInteger('minutes');
            $table->unsignedSmallInteger('hourly_rate');
            $table->smallInteger('fixed_amount')->nullable();
            $table->unsignedBigInteger('report_item_category_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('work_entry_id')->nullable();
            $table->timestamps();
        });

        Schema::create('report_item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });

        Schema::create('work_entries', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->unsignedBigInteger('report_item_category_id')->nullable();
            $table->unsignedBigInteger('report_item_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number');
            $table->datetime('billing_start');
            $table->datetime('billing_end');
            $table->datetime('issued_at')->nullable();
            $table->datetime('due_at')->nullable();
            $table->boolean('paid')->default(0);
            $table->json('client_info');
            $table->json('items');
            $table->integer('subtotal');
            $table->integer('tax');
            $table->integer('total');
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedSmallInteger('estimate_minutes')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->datetime('paid_at');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->integer('amount');
            $table->unsignedBigInteger('organization_id');
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
        Schema::dropIfExists('initial_tables');
    }
}

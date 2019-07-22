<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateCouponsTable extends Migration { public function up() { Schema::create('coupons', function (Blueprint $sp6ab302) { $sp6ab302->increments('id'); $sp6ab302->integer('user_id')->index(); $sp6ab302->integer('category_id')->default(-1); $sp6ab302->integer('product_id')->default(-1); $sp6ab302->integer('type')->default(\App\Coupon::TYPE_REPEAT); $sp6ab302->integer('status')->default(\App\Coupon::STATUS_NORMAL); $sp6ab302->string('coupon', 100)->index(); $sp6ab302->integer('discount_type'); $sp6ab302->integer('discount_val'); $sp6ab302->integer('count_used')->default(0); $sp6ab302->integer('count_all')->default(1); $sp6ab302->string('remark')->nullable(); $sp6ab302->dateTime('expire_at')->nullable(); $sp6ab302->timestamps(); }); } public function down() { Schema::dropIfExists('coupons'); } }
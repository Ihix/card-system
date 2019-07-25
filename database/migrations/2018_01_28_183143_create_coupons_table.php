<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateCouponsTable extends Migration { public function up() { Schema::create('coupons', function (Blueprint $sp232d91) { $sp232d91->increments('id'); $sp232d91->integer('user_id')->index(); $sp232d91->integer('category_id')->default(-1); $sp232d91->integer('product_id')->default(-1); $sp232d91->integer('type')->default(\App\Coupon::TYPE_REPEAT); $sp232d91->integer('status')->default(\App\Coupon::STATUS_NORMAL); $sp232d91->string('coupon', 100)->index(); $sp232d91->integer('discount_type'); $sp232d91->integer('discount_val'); $sp232d91->integer('count_used')->default(0); $sp232d91->integer('count_all')->default(1); $sp232d91->string('remark')->nullable(); $sp232d91->dateTime('expire_at')->nullable(); $sp232d91->timestamps(); }); } public function down() { Schema::dropIfExists('coupons'); } }
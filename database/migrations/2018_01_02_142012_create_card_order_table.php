<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateCardOrderTable extends Migration { public function up() { Schema::create('card_order', function (Blueprint $sp6ab302) { $sp6ab302->increments('id'); $sp6ab302->integer('order_id')->index(); $sp6ab302->integer('card_id'); }); } public function down() { Schema::dropIfExists('card_order'); } }
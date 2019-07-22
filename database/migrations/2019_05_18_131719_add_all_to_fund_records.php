<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class AddAllToFundRecords extends Migration { public function up() { if (!Schema::hasColumn('fund_records', 'all')) { Schema::table('fund_records', function (Blueprint $sp6ab302) { $sp6ab302->integer('all')->nullable()->after('amount'); $sp6ab302->integer('frozen')->nullable()->after('all'); $sp6ab302->integer('paid')->nullable()->after('frozen'); }); } } public function down() { foreach (array('all', 'frozen', 'paid') as $spf39863) { try { Schema::table('fund_records', function (Blueprint $sp6ab302) use($spf39863) { $sp6ab302->dropColumn($spf39863); }); } catch (\Throwable $sp81eee8) { } } } }
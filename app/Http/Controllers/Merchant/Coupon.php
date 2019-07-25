<?php
namespace App\Http\Controllers\Merchant; use App\Library\Response; use Carbon\Carbon; use Illuminate\Http\Request; use App\Http\Controllers\Controller; class Coupon extends Controller { function get(Request $sp054aa0) { $sp0964e2 = $this->authQuery($sp054aa0, \App\Coupon::class)->with(array('category' => function ($sp0964e2) { $sp0964e2->select(array('id', 'name')); }))->with(array('product' => function ($sp0964e2) { $sp0964e2->select(array('id', 'name')); })); $spcb6e4b = $sp054aa0->post('search', false); $spe07b43 = $sp054aa0->post('val', false); if ($spcb6e4b && $spe07b43) { if ($spcb6e4b == 'id') { $sp0964e2->where('id', $spe07b43); } else { $sp0964e2->where($spcb6e4b, 'like', '%' . $spe07b43 . '%'); } } $spc3ee02 = (int) $sp054aa0->post('category_id'); $sp107f34 = $sp054aa0->post('product_id', -1); if ($spc3ee02 > 0) { if ($sp107f34 > 0) { $sp0964e2->where('product_id', $sp107f34); } else { $sp0964e2->where('category_id', $spc3ee02); } } $sp4e5656 = $sp054aa0->post('status'); if (strlen($sp4e5656)) { $sp0964e2->whereIn('status', explode(',', $sp4e5656)); } $sp8beb2b = $sp054aa0->post('type'); if (strlen($sp8beb2b)) { $sp0964e2->whereIn('type', explode(',', $sp8beb2b)); } $sp0964e2->orderByRaw('expire_at DESC,category_id,product_id,type,status'); $sp1d90fd = $sp054aa0->post('current_page', 1); $sp21d879 = $sp054aa0->post('per_page', 20); $sp03b529 = $sp0964e2->paginate($sp21d879, array('*'), 'page', $sp1d90fd); return Response::success($sp03b529); } function create(Request $sp054aa0) { $sp5df9fd = $sp054aa0->post('count', 0); $sp8beb2b = (int) $sp054aa0->post('type', \App\Coupon::TYPE_ONETIME); $spe8b774 = $sp054aa0->post('expire_at'); $sp9973c7 = (int) $sp054aa0->post('discount_val'); $spa90ab5 = (int) $sp054aa0->post('discount_type', \App\Coupon::DISCOUNT_TYPE_AMOUNT); $sp93f669 = $sp054aa0->post('remark'); if ($spa90ab5 === \App\Coupon::DISCOUNT_TYPE_AMOUNT) { if ($sp9973c7 < 1 || $sp9973c7 > 1000000000) { return Response::fail('优惠券面额需要在0.01-10000000之间'); } } if ($spa90ab5 === \App\Coupon::DISCOUNT_TYPE_PERCENT) { if ($sp9973c7 < 1 || $sp9973c7 > 100) { return Response::fail('优惠券面额需要在1-100之间'); } } $spc3ee02 = (int) $sp054aa0->post('category_id', -1); $sp107f34 = (int) $sp054aa0->post('product_id', -1); if ($sp8beb2b === \App\Coupon::TYPE_REPEAT) { $sp4a0347 = $sp054aa0->post('coupon'); if (!$sp4a0347) { $sp4a0347 = strtoupper(str_random()); } $sp12edae = new \App\Coupon(); $sp12edae->user_id = $this->getUserIdOrFail($sp054aa0); $sp12edae->category_id = $spc3ee02; $sp12edae->product_id = $sp107f34; $sp12edae->coupon = $sp4a0347; $sp12edae->type = $sp8beb2b; $sp12edae->discount_val = $sp9973c7; $sp12edae->discount_type = $spa90ab5; $sp12edae->count_all = (int) $sp054aa0->post('count_all', 1); if ($sp12edae->count_all < 1 || $sp12edae->count_all > 10000000) { return Response::fail('可用次数不能超过10000000'); } $sp12edae->expire_at = $spe8b774; $sp12edae->saveOrFail(); return Response::success(array($sp12edae->coupon)); } elseif ($sp8beb2b === \App\Coupon::TYPE_ONETIME) { if (!$sp5df9fd) { return Response::forbidden('请输入生成数量'); } if ($sp5df9fd > 100) { return Response::forbidden('每次生成不能大于100张'); } $spb3fd71 = array(); $sp9cffe8 = array(); $sp47762c = $this->getUserIdOrFail($sp054aa0); $sp11b838 = Carbon::now(); for ($spbc3a4f = 0; $spbc3a4f < $sp5df9fd; $spbc3a4f++) { $sp12edae = strtoupper(str_random()); $sp9cffe8[] = $sp12edae; $spb3fd71[] = array('user_id' => $sp47762c, 'coupon' => $sp12edae, 'category_id' => $spc3ee02, 'product_id' => $sp107f34, 'type' => $sp8beb2b, 'discount_val' => $sp9973c7, 'discount_type' => $spa90ab5, 'status' => \App\Coupon::STATUS_NORMAL, 'remark' => $sp93f669, 'created_at' => $sp11b838, 'expire_at' => $spe8b774); } \App\Coupon::insert($spb3fd71); return Response::success($sp9cffe8); } else { return Response::forbidden('unknown type: ' . $sp8beb2b); } } function edit(Request $sp054aa0) { $spde29a5 = (int) $sp054aa0->post('id'); $sp4a0347 = $sp054aa0->post('coupon'); $spc3ee02 = (int) $sp054aa0->post('category_id', -1); $sp107f34 = (int) $sp054aa0->post('product_id', -1); $spe8b774 = $sp054aa0->post('expire_at', NULL); $sp4e5656 = (int) $sp054aa0->post('status', \App\Coupon::STATUS_NORMAL); $sp8beb2b = (int) $sp054aa0->post('type', \App\Coupon::TYPE_ONETIME); $sp9973c7 = (int) $sp054aa0->post('discount_val'); $spa90ab5 = (int) $sp054aa0->post('discount_type', \App\Coupon::DISCOUNT_TYPE_AMOUNT); if ($spa90ab5 === \App\Coupon::DISCOUNT_TYPE_AMOUNT) { if ($sp9973c7 < 1 || $sp9973c7 > 1000000000) { return Response::fail('优惠券面额需要在0.01-10000000之间'); } } if ($spa90ab5 === \App\Coupon::DISCOUNT_TYPE_PERCENT) { if ($sp9973c7 < 1 || $sp9973c7 > 100) { return Response::fail('优惠券面额需要在1-100之间'); } } $sp12edae = $this->authQuery($sp054aa0, \App\Coupon::class)->find($spde29a5); if ($sp12edae) { $sp12edae->coupon = $sp4a0347; $sp12edae->category_id = $spc3ee02; $sp12edae->product_id = $sp107f34; $sp12edae->status = $sp4e5656; $sp12edae->type = $sp8beb2b; $sp12edae->discount_val = $sp9973c7; $sp12edae->discount_type = $spa90ab5; if ($sp8beb2b === \App\Coupon::TYPE_REPEAT) { $sp12edae->count_all = (int) $sp054aa0->post('count_all', 1); if ($sp12edae->count_all < 1 || $sp12edae->count_all > 10000000) { return Response::fail('可用次数不能超过10000000'); } } if ($spe8b774) { $sp12edae->expire_at = $spe8b774; } $sp12edae->saveOrFail(); } else { $sp458914 = explode('
', $sp4a0347); for ($spbc3a4f = 0; $spbc3a4f < count($sp458914); $spbc3a4f++) { $sp5e04e0 = str_replace('
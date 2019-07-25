<?php
namespace App\Http\Controllers\Shop; use App\Category; use App\Product; use App\Library\Response; use Carbon\Carbon; use Illuminate\Http\Request; use App\Http\Controllers\Controller; class Coupon extends Controller { function info(Request $sp054aa0) { $spc3ee02 = (int) $sp054aa0->post('category_id', -1); $sp107f34 = (int) $sp054aa0->post('product_id', -1); $sp4a0347 = $sp054aa0->post('coupon'); if (!$sp4a0347) { return Response::fail('请输入优惠券'); } if ($spc3ee02 > 0) { $sp4a59d6 = Category::findOrFail($spc3ee02); $sp47762c = $sp4a59d6->user_id; } elseif ($sp107f34 > 0) { $sp648779 = Product::findOrFail($sp107f34); $sp47762c = $sp648779->user_id; } else { return Response::fail('请先选择分类或商品'); } $spb3fd71 = \App\Coupon::where('user_id', $sp47762c)->where('coupon', $sp4a0347)->where('expire_at', '>', Carbon::now())->whereRaw('`count_used`<`count_all`')->get(); foreach ($spb3fd71 as $sp4a0347) { if ($sp4a0347->category_id === -1 || $sp4a0347->category_id === $spc3ee02 && ($sp4a0347->product_id === -1 || $sp4a0347->product_id === $sp107f34)) { $sp4a0347->setVisible(array('discount_type', 'discount_val')); return Response::success($sp4a0347); } } return Response::fail('您输入的优惠券信息无效<br>如果没有优惠券请不要填写'); } }
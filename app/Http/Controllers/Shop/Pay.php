<?php
namespace App\Http\Controllers\Shop; use App\Card; use App\Category; use App\Library\FundHelper; use App\Library\Helper; use App\Library\LogHelper; use App\Product; use App\Library\Response; use App\Library\Pay\Pay as PayApi; use App\Library\Geetest; use App\Mail\OrderShipped; use App\Mail\ProductCountWarn; use App\System; use Carbon\Carbon; use Illuminate\Database\Eloquent\Relations\Relation; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Cookie; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Log; use Illuminate\Support\Facades\Mail; class Pay extends Controller { public function __construct() { define('SYS_NAME', config('app.name')); define('SYS_URL', config('app.url')); define('SYS_URL_API', config('app.url_api')); } private $payApi = null; public function goPay($spfeab54, $sp7c88f3, $sp403be9, $spdc9a36, $spbccd60) { try { (new PayApi())->goPay($spdc9a36, $sp7c88f3, $sp403be9, $sp403be9, $spbccd60); return self::renderResultPage($spfeab54, array('success' => false, 'title' => '请稍后', 'msg' => '支付方式加载中，请稍后')); } catch (\Exception $sp81eee8) { return self::renderResultPage($spfeab54, array('msg' => $sp81eee8->getMessage())); } } function buy(Request $spfeab54) { $sp0daafb = $spfeab54->input('customer'); if (strlen($sp0daafb) !== 32) { return self::renderResultPage($spfeab54, array('msg' => '提交超时，请刷新购买页面并重新提交<br><br>
当前网址: ' . $spfeab54->getQueryString() . '
提交内容: ' . var_export($sp0daafb) . ', 提交长度:' . strlen($sp0daafb) . '<br>
若您刷新后仍然出现此问题. 请加网站客服反馈')); } if ((int) System::_get('vcode_shop_buy') === 1) { $spb9589c = Geetest\API::verify($spfeab54->input('geetest_challenge'), $spfeab54->input('geetest_validate'), $spfeab54->input('geetest_seccode')); if (!$spb9589c) { return self::renderResultPage($spfeab54, array('msg' => '滑动验证超时，请返回页面重试。')); } } $sp790613 = (int) $spfeab54->input('category_id'); $sp0e30a6 = (int) $spfeab54->input('product_id'); $spfdb02a = (int) $spfeab54->input('count'); $spd5c9d2 = $spfeab54->input('coupon'); $sp09279e = $spfeab54->input('contact'); $sp5e5f4d = $spfeab54->input('contact_ext') ?? null; $spb5f208 = !empty(@json_decode($sp5e5f4d, true)['_mobile']); $sp15a8b5 = (int) $spfeab54->input('pay_id'); if (!$sp790613 || !$sp0e30a6) { return self::renderResultPage($spfeab54, array('msg' => '请选择商品')); } if (strlen($sp09279e) < 1) { return self::renderResultPage($spfeab54, array('msg' => '请输入联系方式')); } $spb7fea4 = Category::findOrFail($sp790613); $sp71cb0c = Product::where('id', $sp0e30a6)->where('category_id', $sp790613)->where('enabled', 1)->with(array('user'))->first(); if ($sp71cb0c == null || $sp71cb0c->user == null) { return self::renderResultPage($spfeab54, array('msg' => '该商品未找到，请重新选择')); } if ($sp71cb0c->password_open) { if ($sp71cb0c->password !== $spfeab54->input('product_password')) { return self::renderResultPage($spfeab54, array('msg' => '商品密码输入错误')); } } else { if ($spb7fea4->password_open) { if ($spb7fea4->password !== $spfeab54->input('category_password')) { if ($spb7fea4->getTmpPassword() !== $spfeab54->input('category_password')) { return self::renderResultPage($spfeab54, array('msg' => '分类密码输入错误')); } } } } if ($spfdb02a < $sp71cb0c->buy_min) { return self::renderResultPage($spfeab54, array('msg' => '该商品最少购买' . $sp71cb0c->buy_min . '件，请重新选择')); } if ($spfdb02a > $sp71cb0c->buy_max) { return self::renderResultPage($spfeab54, array('msg' => '该商品限购' . $sp71cb0c->buy_max . '件，请重新选择')); } if ($sp71cb0c->count < $spfdb02a) { return self::renderResultPage($spfeab54, array('msg' => '该商品库存不足')); } $spd46fd8 = \App\Pay::find($sp15a8b5); if ($spd46fd8 == null || !$spd46fd8->enabled) { return self::renderResultPage($spfeab54, array('msg' => '支付方式未找到，请重新选择')); } $spf60d59 = $sp71cb0c->price; if ($sp71cb0c->price_whole) { $sp4ab772 = json_decode($sp71cb0c->price_whole, true); for ($spea591f = count($sp4ab772) - 1; $spea591f >= 0; $spea591f--) { if ($spfdb02a >= (int) $sp4ab772[$spea591f][0]) { $spf60d59 = (int) $sp4ab772[$spea591f][1]; break; } } } $sp62b824 = $spfdb02a * $spf60d59; $spbccd60 = $sp62b824; $spafa6ef = 0; $sp6be256 = null; if ($sp71cb0c->support_coupon && strlen($spd5c9d2) > 0) { $spdacc62 = \App\Coupon::where('user_id', $sp71cb0c->user_id)->where('coupon', $spd5c9d2)->where('expire_at', '>', Carbon::now())->whereRaw('`count_used`<`count_all`')->get(); foreach ($spdacc62 as $sp6fac42) { if ($sp6fac42->category_id === -1 || $sp6fac42->category_id === $sp790613 && ($sp6fac42->product_id === -1 || $sp6fac42->product_id === $sp0e30a6)) { if ($sp6fac42->discount_type === \App\Coupon::DISCOUNT_TYPE_AMOUNT && $spbccd60 >= $sp6fac42->discount_val) { $sp6be256 = $sp6fac42; $spafa6ef = $sp6fac42->discount_val; break; } if ($sp6fac42->discount_type === \App\Coupon::DISCOUNT_TYPE_PERCENT) { $sp6be256 = $sp6fac42; $spafa6ef = (int) round($spbccd60 * $sp6fac42->discount_val / 100); break; } } } if ($sp6be256 === null) { return self::renderResultPage($spfeab54, array('msg' => '优惠券信息错误，请重新输入')); } $spbccd60 -= $spafa6ef; } $sp7752ec = (int) round($spbccd60 * $spd46fd8->fee_system); $sp950db8 = $spbccd60 - $sp7752ec; $sp06862b = $spb5f208 ? System::_getInt('sms_price', 10) : 0; $spbccd60 += $sp06862b; $sp585cd5 = $spfdb02a * $sp71cb0c->cost; $sp7c88f3 = \App\Order::unique_no(); try { DB::transaction(function () use($sp71cb0c, $sp7c88f3, $sp6be256, $sp09279e, $sp5e5f4d, $sp0daafb, $spfdb02a, $sp585cd5, $sp62b824, $sp06862b, $spafa6ef, $spbccd60, $spd46fd8, $sp7752ec, $sp950db8) { if ($sp6be256) { $sp6be256->status = \App\Coupon::STATUS_USED; $sp6be256->count_used++; $sp6be256->save(); $sp894a06 = '使用优惠券: ' . $sp6be256->coupon; } else { $sp894a06 = null; } $sp4f4c0d = \App\Order::create(array('user_id' => $sp71cb0c->user_id, 'order_no' => $sp7c88f3, 'product_id' => $sp71cb0c->id, 'product_name' => $sp71cb0c->name, 'count' => $spfdb02a, 'ip' => Helper::getIP(), 'customer' => $sp0daafb, 'contact' => $sp09279e, 'contact_ext' => $sp5e5f4d, 'cost' => $sp585cd5, 'price' => $sp62b824, 'sms_price' => $sp06862b, 'discount' => $spafa6ef, 'paid' => $spbccd60, 'pay_id' => $spd46fd8->id, 'fee' => $sp7752ec, 'system_fee' => $sp7752ec, 'income' => $sp950db8, 'status' => \App\Order::STATUS_UNPAY, 'remark' => $sp894a06, 'created_at' => Carbon::now())); assert($sp4f4c0d !== null); }); } catch (\Throwable $sp81eee8) { Log::error('Shop.Pay.buy 下单失败', array('Exception' => $sp81eee8)); return self::renderResultPage($spfeab54, array('msg' => '发生错误，下单失败，请稍后重试')); } if ($spbccd60 === 0) { $this->shipOrder($spfeab54, $sp7c88f3, $spbccd60, null); return redirect('/pay/result/' . $sp7c88f3); } $sp403be9 = $sp7c88f3; return $this->goPay($spfeab54, $sp7c88f3, $sp403be9, $spd46fd8, $spbccd60); } function pay(Request $spfeab54, $sp7c88f3) { $sp4f4c0d = \App\Order::whereOrderNo($sp7c88f3)->first(); if ($sp4f4c0d == null) { return self::renderResultPage($spfeab54, array('msg' => '订单未找到，请重试')); } if ($sp4f4c0d->status !== \App\Order::STATUS_UNPAY) { return redirect('/pay/result/' . $sp7c88f3); } $sp9df21a = 'pay: ' . $sp4f4c0d->pay_id; $spdc9a36 = $sp4f4c0d->pay; if (!$spdc9a36) { \Log::error($sp9df21a . ' cannot find Pay'); return $this->renderResultPage($spfeab54, array('msg' => '支付方式未找到')); } $sp9df21a .= ',' . $spdc9a36->driver; $spbe80b7 = json_decode($spdc9a36->config, true); $spbe80b7['payway'] = $spdc9a36->way; $spbe80b7['out_trade_no'] = $sp7c88f3; try { $this->payApi = PayApi::getDriver($spdc9a36->id, $spdc9a36->driver); } catch (\Exception $sp81eee8) { \Log::error($sp9df21a . ' cannot find Driver: ' . $sp81eee8->getMessage()); return $this->renderResultPage($spfeab54, array('msg' => '支付驱动未找到')); } if ($this->payApi->verify($spbe80b7, function ($sp7c88f3, $sp429fcc, $sp4d48a7) use($spfeab54) { try { $this->shipOrder($spfeab54, $sp7c88f3, $sp429fcc, $sp4d48a7); } catch (\Exception $sp81eee8) { $this->renderResultPage($spfeab54, array('success' => false, 'msg' => $sp81eee8->getMessage())); } })) { \Log::notice($sp9df21a . ' already success' . '

'); return redirect('/pay/result/' . $sp7c88f3); } if ($sp4f4c0d->created_at < Carbon::now()->addMinutes(-5)) { return $this->renderResultPage($spfeab54, array('msg' => '当前订单长时间未支付已作废, 请重新下单')); } $sp71cb0c = Product::where('id', $sp4f4c0d->product_id)->where('enabled', 1)->first(); if ($sp71cb0c == null) { return self::renderResultPage($spfeab54, array('msg' => '该商品已下架')); } $sp71cb0c->setAttribute('count', count($sp71cb0c->cards) ? $sp71cb0c->cards[0]->count : 0); if ($sp71cb0c->count < $sp4f4c0d->count) { return self::renderResultPage($spfeab54, array('msg' => '该商品库存不足')); } $sp403be9 = $sp7c88f3; return $this->goPay($spfeab54, $sp7c88f3, $sp403be9, $spdc9a36, $sp4f4c0d->paid); } function qrcode(Request $spfeab54, $sp7c88f3, $sp1e8720) { $sp4f4c0d = \App\Order::whereOrderNo($sp7c88f3)->with('product')->first(); if ($sp4f4c0d == null) { return self::renderResultPage($spfeab54, array('msg' => '订单未找到，请重试')); } if ($sp4f4c0d->product_id !== \App\Product::ID_API && $sp4f4c0d->product == null) { return self::renderResultPage($spfeab54, array('msg' => '商品未找到，请重试')); } return view('pay/' . $sp1e8720, array('pay_id' => $sp4f4c0d->pay_id, 'name' => $sp4f4c0d->product->name . ' x ' . $sp4f4c0d->count . '件', 'amount' => $sp4f4c0d->paid, 'qrcode' => $spfeab54->get('url'), 'id' => $sp7c88f3)); } function qrQuery(Request $spfeab54, $sp15a8b5) { $spc7b84a = $spfeab54->input('id', ''); return self::payReturn($spfeab54, $sp15a8b5, $spc7b84a); } function payReturn(Request $spfeab54, $sp15a8b5, $spa3e681 = '') { $sp9df21a = 'payReturn: ' . $sp15a8b5; \Log::debug($sp9df21a); $spdc9a36 = \App\Pay::where('id', $sp15a8b5)->first(); if (!$spdc9a36) { return $this->renderResultPage($spfeab54, array('success' => 0, 'msg' => '支付方式错误')); } $sp9df21a .= ',' . $spdc9a36->driver; if (strlen($spa3e681) > 0) { $sp4f4c0d = \App\Order::whereOrderNo($spa3e681)->first(); if ($sp4f4c0d && ($sp4f4c0d->status === \App\Order::STATUS_PAID || $sp4f4c0d->status === \App\Order::STATUS_SUCCESS)) { \Log::notice($sp9df21a . ' already success' . '

'); if ($spfeab54->ajax()) { return self::renderResultPage($spfeab54, array('success' => 1, 'data' => '/pay/result/' . $spa3e681), array('order' => $sp4f4c0d)); } else { return redirect('/pay/result/' . $spa3e681); } } } try { $this->payApi = PayApi::getDriver($spdc9a36->id, $spdc9a36->driver); } catch (\Exception $sp81eee8) { \Log::error($sp9df21a . ' cannot find Driver: ' . $sp81eee8->getMessage()); return $this->renderResultPage($spfeab54, array('success' => 0, 'msg' => '支付驱动未找到')); } $spbe80b7 = json_decode($spdc9a36->config, true); $spbe80b7['out_trade_no'] = $spa3e681; $spbe80b7['payway'] = $spdc9a36->way; \Log::debug($sp9df21a . ' will verify'); if ($this->payApi->verify($spbe80b7, function ($sp7c88f3, $sp429fcc, $sp4d48a7) use($spfeab54, $sp9df21a, &$spa3e681) { $spa3e681 = $sp7c88f3; try { \Log::debug($sp9df21a . " shipOrder start, order_no: {$sp7c88f3}, amount: {$sp429fcc}, trade_no: {$sp4d48a7}"); $this->shipOrder($spfeab54, $sp7c88f3, $sp429fcc, $sp4d48a7); \Log::debug($sp9df21a . ' shipOrder end, order_no: ' . $sp7c88f3); } catch (\Exception $sp81eee8) { \Log::error($sp9df21a . ' shipOrder Exception: ' . $sp81eee8->getMessage()); } })) { \Log::debug($sp9df21a . ' verify finished: 1' . '

'); if ($spfeab54->ajax()) { return self::renderResultPage($spfeab54, array('success' => 1, 'data' => '/pay/result/' . $spa3e681)); } else { return redirect('/pay/result/' . $spa3e681); } } else { \Log::debug($sp9df21a . ' verify finished: 0' . '

'); return $this->renderResultPage($spfeab54, array('success' => 0, 'msg' => '支付验证失败，您可以稍后查看支付状态。')); } } function payNotify(Request $spfeab54, $sp15a8b5) { $sp9df21a = 'payNotify pay_id: ' . $sp15a8b5; \Log::debug($sp9df21a); $spdc9a36 = \App\Pay::where('id', $sp15a8b5)->first(); if (!$spdc9a36) { \Log::error($sp9df21a . ' cannot find PayModel'); echo 'fail'; die; } $sp9df21a .= ',' . $spdc9a36->driver; try { $this->payApi = PayApi::getDriver($spdc9a36->id, $spdc9a36->driver); } catch (\Exception $sp81eee8) { \Log::error($sp9df21a . ' cannot find Driver: ' . $sp81eee8->getMessage()); echo 'fail'; die; } $spbe80b7 = json_decode($spdc9a36->config, true); $spbe80b7['payway'] = $spdc9a36->way; $spbe80b7['isNotify'] = true; \Log::debug($sp9df21a . ' will verify'); $spb9589c = $this->payApi->verify($spbe80b7, function ($sp7c88f3, $sp429fcc, $sp4d48a7) use($spfeab54, $sp9df21a) { try { \Log::debug($sp9df21a . " shipOrder start, order_no: {$sp7c88f3}, amount: {$sp429fcc}, trade_no: {$sp4d48a7}"); $this->shipOrder($spfeab54, $sp7c88f3, $sp429fcc, $sp4d48a7); \Log::debug($sp9df21a . ' shipOrder end, order_no: ' . $sp7c88f3); } catch (\Exception $sp81eee8) { \Log::error($sp9df21a . ' shipOrder Exception: ' . $sp81eee8->getMessage()); } }); \Log::debug($sp9df21a . ' notify finished: ' . (int) $spb9589c . '

'); die; } function result(Request $spfeab54, $sp7c88f3) { $sp4f4c0d = \App\Order::where('order_no', $sp7c88f3)->first(); if ($sp4f4c0d == null) { return self::renderResultPage($spfeab54, array('msg' => '订单未找到，请重试')); } if ($sp4f4c0d->status === \App\Order::STATUS_PAID) { $sp18d561 = $sp4f4c0d->user->qq; if ($sp4f4c0d->product->delivery === \App\Product::DELIVERY_MANUAL) { $sp093e23 = '您购买的为手动充值商品，请耐心等待处理'; } else { $sp093e23 = '商家库存不足，因此没有自动发货，请联系商家客服发货'; } if ($sp18d561) { $sp093e23 .= '<br><a href="http://wpa.qq.com/msgrd?v=3&uin=' . $sp18d561 . '&site=qq&menu=yes" target="_blank">客服QQ:' . $sp18d561 . '</a>'; } return self::renderResultPage($spfeab54, array('success' => false, 'title' => '订单已支付', 'msg' => $sp093e23), array('order' => $sp4f4c0d)); } elseif ($sp4f4c0d->status === \App\Order::STATUS_SUCCESS) { return self::showOrderResult($spfeab54, $sp4f4c0d); } return self::renderResultPage($spfeab54, array('success' => false, 'msg' => $sp4f4c0d->remark ? '失败原因:<br>' . $sp4f4c0d->remark : '订单支付失败，请重试'), array('order' => $sp4f4c0d)); } function renderResultPage(Request $spfeab54, $spbbda25, $spee2ceb = array()) { if ($spfeab54->ajax()) { if (@$spbbda25['success']) { return Response::success($spbbda25['data']); } else { return Response::fail('error', $spbbda25['msg']); } } else { return view('pay.result', array_merge(array('result' => $spbbda25, 'data' => $spee2ceb), $spee2ceb)); } } function shipOrder($spfeab54, $sp7c88f3, $sp429fcc, $sp4d48a7) { $sp4f4c0d = \App\Order::whereOrderNo($sp7c88f3)->first(); if ($sp4f4c0d === null) { \Log::error('shipOrder: No query results for model [App\\Order:' . $sp7c88f3 . ',trade_no:' . $sp4d48a7 . ',amount:' . $sp429fcc . ']. die(\'success\');'); die('success'); } if ($sp4f4c0d->paid > $sp429fcc) { \Log::alert('shipOrder, price may error, order_no:' . $sp7c88f3 . ', paid:' . $sp4f4c0d->paid . ', $amount get:' . $sp429fcc); $sp4f4c0d->remark = '支付金额(' . sprintf('%0.2f', $sp429fcc / 100) . ') 小于 订单金额(' . sprintf('%0.2f', $sp4f4c0d->paid / 100) . ')'; $sp4f4c0d->save(); throw new \Exception($sp4f4c0d->remark); } $sp71cb0c = null; if ($sp4f4c0d->status === \App\Order::STATUS_UNPAY) { \Log::debug('shipOrder.first_process:' . $sp7c88f3); $sp48ee76 = $sp4f4c0d->id; if (FundHelper::orderSuccess($sp4f4c0d->id, function ($spf56da4) use($sp48ee76, $sp4d48a7, &$sp4f4c0d, &$sp71cb0c) { $sp4f4c0d = $spf56da4; if ($sp4f4c0d->status !== \App\Order::STATUS_UNPAY) { \Log::debug('Shop.Pay.shipOrder: .first_process:' . $sp4f4c0d->order_no . ' already processed! #2'); return false; } $sp71cb0c = $sp4f4c0d->product()->lockForUpdate()->firstOrFail(); $sp4f4c0d->pay_trade_no = $sp4d48a7; $sp4f4c0d->paid_at = Carbon::now(); if ($sp71cb0c->delivery === \App\Product::DELIVERY_MANUAL) { $sp4f4c0d->status = \App\Order::STATUS_PAID; $sp4f4c0d->send_status = \App\Order::SEND_STATUS_CARD_UN; $sp4f4c0d->saveOrFail(); return true; } $sp33f345 = Card::where('product_id', $sp4f4c0d->product_id)->whereRaw('`count_sold`<`count_all`')->take($sp4f4c0d->count)->lockForUpdate()->get(); if (count($sp33f345) !== $sp4f4c0d->count) { Log::alert('Shop.Pay.shipOrder: 订单:' . $sp4f4c0d->order_no . ', 购买数量:' . $sp4f4c0d->count . ', 卡数量:' . count($sp33f345) . ' 卡密不足(已支付 未发货)'); $sp4f4c0d->status = \App\Order::STATUS_PAID; $sp4f4c0d->saveOrFail(); return true; } else { $sp011030 = array(); foreach ($sp33f345 as $sp0f75bb) { $sp011030[] = $sp0f75bb->id; } $sp4f4c0d->cards()->attach($sp011030); Card::whereIn('id', $sp011030)->update(array('status' => Card::STATUS_SOLD, 'count_sold' => DB::raw('`count_sold`+1'))); $sp4f4c0d->status = \App\Order::STATUS_SUCCESS; $sp4f4c0d->saveOrFail(); $sp71cb0c->count_sold += $sp4f4c0d->count; $sp71cb0c->saveOrFail(); return FundHelper::ACTION_CONTINUE; } })) { if ($sp71cb0c->count_warn > 0 && $sp71cb0c->count < $sp71cb0c->count_warn) { try { Mail::to($sp4f4c0d->user->email)->Queue(new ProductCountWarn($sp71cb0c, $sp71cb0c->count)); } catch (\Throwable $sp81eee8) { LogHelper::setLogFile('mail'); Log::error('shipOrder.count_warn error', array('product_id' => $sp4f4c0d->product_id, 'email' => $sp4f4c0d->user->email, 'exception' => $sp81eee8->getMessage())); LogHelper::setLogFile('card'); } } if (System::_getInt('mail_send_order')) { $sp4c999d = @json_decode($sp4f4c0d->contact_ext, true)['_mail']; if ($sp4c999d) { $sp4f4c0d->sendEmail($sp4c999d); } } if ($sp4f4c0d->status === \App\Order::STATUS_SUCCESS && System::_getInt('sms_send_order')) { $sp6566f0 = @json_decode($sp4f4c0d->contact_ext, true)['_mobile']; if ($sp6566f0) { $sp4f4c0d->sendSms($sp6566f0); } } } else { } } else { Log::debug('Shop.Pay.shipOrder: .order_no:' . $sp4f4c0d->order_no . ' already processed! #1'); } return FALSE; } private function showOrderResult($spfeab54, $sp4f4c0d) { return self::renderResultPage($spfeab54, array('success' => true, 'msg' => $sp4f4c0d->getSendMessage()), array('card_txt' => join('&#013;&#010;', $sp4f4c0d->getCardsArray()), 'order' => $sp4f4c0d, 'product' => $sp4f4c0d->product)); } }
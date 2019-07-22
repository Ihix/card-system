<?php
namespace App\Library\Pay\Youzan; use App\Library\Pay\ApiInterface; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp53f8aa) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp53f8aa; $this->url_return = SYS_URL . '/pay/return/' . $sp53f8aa; } private function getAccessToken($spbe80b7) { $sp0ec88e = $spbe80b7['client_id']; $sp34b7ec = $spbe80b7['client_secret']; $sp6dc367 = array('kdt_id' => $spbe80b7['kdt_id']); $spbbda25 = (new Open\Token($sp0ec88e, $sp34b7ec))->getToken('self', $sp6dc367); if (!isset($spbbda25['access_token'])) { \Log::error('Pay.Youzan.goPay.getToken Error: ' . json_encode($spbbda25)); throw new \Exception('平台支付Token获取失败'); } return $spbbda25['access_token']; } function goPay($spbe80b7, $spa3e681, $sp45f07e, $sp873488, $sp5213ee) { $sp908e43 = strtolower($spbe80b7['payway']); try { $sp730fcc = $this->getAccessToken($spbe80b7); $spb0d603 = new Open\Client($sp730fcc); } catch (\Exception $sp81eee8) { \Log::error('Pay.Youzan.goPay getAccessToken error', array('exception' => $sp81eee8)); throw new \Exception('支付渠道响应超时，请刷新重试'); } $spa26894 = array('qr_type' => 'QR_TYPE_DYNAMIC', 'qr_price' => $sp5213ee, 'qr_name' => $sp45f07e, 'qr_source' => $spa3e681); $spbbda25 = $spb0d603->get('youzan.pay.qrcode.create', '3.0.0', $spa26894); $spbbda25 = isset($spbbda25['response']) ? $spbbda25['response'] : $spbbda25; if (!isset($spbbda25['qr_url'])) { \Log::error('Pay.Youzan.goPay.getQrcode Error: ' . json_encode($spbbda25)); throw new \Exception('平台支付二维码获取失败'); } \App\Order::whereOrderNo($spa3e681)->update(array('pay_trade_no' => $spbbda25['qr_id'])); header('location: /qrcode/pay/' . $spa3e681 . '/youzan_' . strtolower($sp908e43) . '?url=' . urlencode($spbbda25['qr_url'])); die; } function verify($spbe80b7, $sp04f0f8) { $sp3bce01 = isset($spbe80b7['isNotify']) && $spbe80b7['isNotify']; $sp0ec88e = $spbe80b7['client_id']; $sp34b7ec = $spbe80b7['client_secret']; if ($sp3bce01) { $spa479c2 = file_get_contents('php://input'); $sp5aa598 = json_decode($spa479c2, true); if (@$sp5aa598['test']) { echo 'test success'; return false; } try { $sp093e23 = $sp5aa598['msg']; } catch (\Exception $sp81eee8) { \Log::error('Pay.Youzan.verify get input error#1', array('exception' => $sp81eee8, 'post_raw' => $spa479c2)); echo 'fatal error'; return false; } $sp83414e = $sp0ec88e . '' . $sp093e23 . '' . $sp34b7ec; $spa109d2 = md5($sp83414e); if ($spa109d2 != $sp5aa598['sign']) { \Log::error('Pay.Youzan.verify, sign error $sign_string:' . $sp83414e . ', $sign' . $spa109d2); echo 'fatal error'; return false; } else { echo json_encode(array('code' => 0, 'msg' => 'success')); } $sp093e23 = json_decode(urldecode($sp093e23), true); if ($sp5aa598['type'] === 'TRADE_ORDER_STATE' && $sp093e23['status'] === 'TRADE_SUCCESS') { try { $sp730fcc = $this->getAccessToken($spbe80b7); $spb0d603 = new Open\Client($sp730fcc); } catch (\Exception $sp81eee8) { \Log::error('Pay.Youzan.verify getAccessToken error#1', array('exception' => $sp81eee8)); echo 'fatal error'; return false; } $spa26894 = array('tid' => $sp093e23['tid']); $spbbda25 = $spb0d603->get('youzan.trade.get', '3.0.0', $spa26894); if (isset($spbbda25['error_response'])) { \Log::error('Pay.Youzan.verify with error：' . $spbbda25['error_response']['msg']); echo 'fatal error'; return false; } $sp9af141 = $spbbda25['response']['trade']; $sp4f4c0d = \App\Order::where('pay_trade_no', $sp9af141['qr_id'])->first(); if ($sp4f4c0d) { $spd63ffb = $sp093e23['tid']; $sp04f0f8($sp4f4c0d->order_no, (int) round($sp9af141['payment'] * 100), $spd63ffb); } } return true; } else { $spa3e681 = @$spbe80b7['out_trade_no']; if (strlen($spa3e681) < 5) { throw new \Exception('交易单号未传入'); } $sp4f4c0d = \App\Order::whereOrderNo($spa3e681)->firstOrFail(); if (!$sp4f4c0d->pay_trade_no || !strlen($sp4f4c0d->pay_trade_no)) { return false; } try { $sp730fcc = $this->getAccessToken($spbe80b7); $spb0d603 = new Open\Client($sp730fcc); } catch (\Exception $sp81eee8) { \Log::error('Pay.Youzan.verify getAccessToken error#2', array('exception' => $sp81eee8)); throw new \Exception('支付渠道响应超时，请刷新重试'); } $spa26894 = array('qr_id' => $sp4f4c0d->pay_trade_no, 'status' => 'TRADE_RECEIVED'); $spbbda25 = $spb0d603->get('youzan.trades.qr.get', '3.0.0', $spa26894); $sp9a8711 = isset($spbbda25['response']) ? $spbbda25['response'] : $spbbda25; if (!isset($sp9a8711['total_results'])) { \Log::error('Pay.Youzan.verify with error：The result of [youzan.trades.qr.get] has no key named [total_results]', array('result' => $spbbda25)); return false; } if ($sp9a8711['total_results'] > 0 && count($sp9a8711['qr_trades']) > 0 && isset($sp9a8711['qr_trades'][0]['qr_id']) && $sp9a8711['qr_trades'][0]['qr_id'] === $sp4f4c0d->pay_trade_no) { $spd5ad79 = $sp9a8711['qr_trades'][0]; $spd63ffb = $spd5ad79['tid']; $sp04f0f8($spa3e681, (int) round($spd5ad79['real_price'] * 100), $spd63ffb); return true; } else { return false; } } } }
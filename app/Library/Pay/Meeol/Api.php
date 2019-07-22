<?php
namespace App\Library\Pay\Meeol; use App\Library\CurlRequest; use App\Library\Pay\ApiInterface; use Illuminate\Support\Facades\Log; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp53f8aa) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp53f8aa; $this->url_return = SYS_URL . '/pay/return/' . $sp53f8aa; } function goPay($spbe80b7, $spa3e681, $sp45f07e, $sp873488, $sp5213ee) { $sp9624ba = sprintf('%.2f', $sp5213ee / 100); if (!isset($spbe80b7['appId'])) { throw new \Exception('请设置appId'); } if (!isset($spbe80b7['key'])) { throw new \Exception('请设置key'); } $spdc9a36 = $spbe80b7['payway']; $spe28521 = array('amount' => $sp9624ba, 'appId' => $spbe80b7['appId'], 'orderId' => $spa3e681, 'random' => md5(random_bytes(16)), 'tradeType' => $spdc9a36); $spe28521['sign'] = strtoupper(md5('amount=' . $spe28521['amount'] . '&appId=' . $spbe80b7['appId'] . '&key=' . $spbe80b7['key'] . '&orderId=' . $spe28521['orderId'] . '&random=' . $spe28521['random'] . '&tradeType=' . $spe28521['tradeType'])); $sp00a165 = CurlRequest::post('http://api.meeol.cn/rest/mall/payment/order', json_encode($spe28521)); $spb9589c = json_decode($sp00a165, true); if (!isset($spb9589c['status']) || $spb9589c['status'] !== '0') { Log::error('Pay.Meeol.goPay.order Error: ' . $sp00a165); throw new \Exception('支付请求失败, 请刷新重试'); } if (substr($spdc9a36, 0, 1) === 'W') { header('Location: /qrcode/pay/' . $spa3e681 . '/wechat?url=' . urlencode($spb9589c['qrcode'])); } elseif (substr($spdc9a36, 0, 1) === 'A') { header('Location: /qrcode/pay/' . $spa3e681 . '/aliqr?url=' . urlencode($spb9589c['qrcode'])); } die; } function verify($spbe80b7, $sp04f0f8) { $sp3bce01 = isset($spbe80b7['isNotify']) && $spbe80b7['isNotify']; if ($sp3bce01) { $sp8e4af8 = json_decode(file_get_contents('php://input'), true); $spa109d2 = strtoupper(md5('amount=' . $sp8e4af8['amount'] . '&appid=' . $sp8e4af8['appid'] . '&key=' . $spbe80b7['key'] . '&orderId=' . $sp8e4af8['orderId'] . '&tradeTime=' . $sp8e4af8['tradeTime'] . '&tradeType=' . $sp8e4af8['tradeType'])); if ($spa109d2 === $sp8e4af8['sign']) { $sp9624ba = (int) round($sp8e4af8['amount'] * 100); $sp04f0f8($sp8e4af8['orderId'], $sp9624ba, $sp8e4af8['passTradeNo']); echo 'success'; return true; } else { Log::error('Pay.Meeol.verify notify sign error, post: ' . file_get_contents('php://input')); echo 'error'; } } else { if (!empty($spbe80b7['out_trade_no'])) { $spe28521 = array('appId' => $spbe80b7['appId'], 'orderId' => $spbe80b7['out_trade_no'], 'random' => md5(random_bytes(16))); $spe28521['sign'] = strtoupper(md5('appId=' . $spbe80b7['appId'] . '&key=' . $spbe80b7['key'] . '&orderId=' . $spe28521['orderId'] . '&random=' . $spe28521['random'])); $spe28521 = json_encode($spe28521); $sp00a165 = CurlRequest::post('http://api.meeol.cn/rest/mall/payment/query', $spe28521); $spb9589c = json_decode($sp00a165, true); if (!isset($spb9589c['status'])) { Log::error('Pay.Meeol.verify Error: ' . $sp00a165); } if ($spb9589c['status'] === '0') { $sp9624ba = (int) round($spb9589c['amount'] * 100); $sp04f0f8($spb9589c['orderId'], $sp9624ba, $spb9589c['passTradeNo']); return true; } Log::debug('Pay.Meeol.verify debug, req:' . $spe28521 . 'ret:' . $sp00a165); return false; } else { throw new \Exception('请传递订单编号'); } } return false; } }
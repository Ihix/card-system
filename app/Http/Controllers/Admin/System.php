<?php
namespace App\Http\Controllers\Admin; use App\Library\Helper; use App\Library\Response; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Mail; class System extends Controller { private function set(Request $spfeab54, $sp342c1c) { foreach ($sp342c1c as $sp505b52) { if ($spfeab54->has($sp505b52)) { \App\System::_set($sp505b52, $spfeab54->post($sp505b52)); } } } private function setMoney(Request $spfeab54, $sp342c1c) { foreach ($sp342c1c as $sp505b52) { if ($spfeab54->has($sp505b52)) { \App\System::_set($sp505b52, (int) round($spfeab54->post($sp505b52) * 100)); } } } private function setInt(Request $spfeab54, $sp342c1c) { foreach ($sp342c1c as $sp505b52) { if ($spfeab54->has($sp505b52)) { \App\System::_set($sp505b52, (int) $spfeab54->post($sp505b52)); } } } function setItem(Request $spfeab54) { $sp505b52 = $spfeab54->post('name'); $spb914e6 = $spfeab54->post('value'); if (!$sp505b52 || !$spb914e6) { return Response::forbidden(); } \App\System::_set($sp505b52, $spb914e6); return Response::success(); } function info(Request $spfeab54) { $spfb8dfb = array('app_name', 'app_title', 'app_url', 'app_url_api', 'keywords', 'description', 'shop_ann', 'shop_ann_pop', 'shop_qq', 'company', 'js_tj', 'js_kf'); $sp397a5c = array('shop_inventory'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($spfb8dfb as $sp505b52) { $spbe80b7[$sp505b52] = \App\System::_get($sp505b52); } foreach ($sp397a5c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $sp3db1b2 = array('app_url' => Helper::format_url($_POST['app_url']), 'app_url_api' => Helper::format_url($_POST['app_url_api'])); $spfeab54->merge($sp3db1b2); $this->set($spfeab54, $spfb8dfb); $this->setInt($spfeab54, $sp397a5c); return Response::success(); } function theme(Request $spfeab54) { if ($spfeab54->isMethod('GET')) { \App\ShopTheme::freshList(); return Response::success(array('themes' => \App\ShopTheme::get(), 'default' => \App\ShopTheme::defaultTheme()->name)); } $sp1f7915 = \App\ShopTheme::whereName($spfeab54->post('shop_theme'))->firstOrFail(); \App\System::_set('shop_theme_default', $sp1f7915->name); $sp1f7915->config = @json_decode($spfeab54->post('theme_config')) ?? array(); $sp1f7915->saveOrFail(); return Response::success(); } function order(Request $spfeab54) { $sp342c1c = array('order_clean_unpay_open', 'order_clean_unpay_day'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($sp342c1c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $this->setInt($spfeab54, $sp342c1c); return Response::success(); } function vcode(Request $spfeab54) { $spfb8dfb = array('vcode_driver', 'vcode_geetest_id', 'vcode_geetest_key'); $sp397a5c = array('vcode_login', 'vcode_shop_buy', 'vcode_shop_search'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($spfb8dfb as $sp505b52) { $spbe80b7[$sp505b52] = \App\System::_get($sp505b52); } foreach ($sp397a5c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $this->set($spfeab54, $spfb8dfb); $this->setInt($spfeab54, $sp397a5c); return Response::success(); } function email(Request $spfeab54) { $spfb8dfb = array('mail_driver', 'mail_smtp_host', 'mail_smtp_port', 'mail_smtp_username', 'mail_smtp_password', 'mail_smtp_from_address', 'mail_smtp_from_name', 'mail_smtp_encryption', 'sendcloud_user', 'sendcloud_key'); $sp397a5c = array('mail_send_order'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($spfb8dfb as $sp505b52) { $spbe80b7[$sp505b52] = \App\System::_get($sp505b52); } foreach ($sp397a5c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $this->set($spfeab54, $spfb8dfb); $this->setInt($spfeab54, $sp397a5c); return Response::success(); } function sms(Request $spfeab54) { $spfb8dfb = array('sms_api_id', 'sms_api_key'); $sp397a5c = array('sms_send_order', 'sms_price'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($spfb8dfb as $sp505b52) { $spbe80b7[$sp505b52] = \App\System::_get($sp505b52); } foreach ($sp397a5c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $this->set($spfeab54, $spfb8dfb); $this->setInt($spfeab54, $sp397a5c); return Response::success(); } function storage(Request $spfeab54) { $spfb8dfb = array('storage_driver', 'storage_s3_access_key', 'storage_s3_secret_key', 'storage_s3_region', 'storage_s3_bucket', 'storage_oss_access_key', 'storage_oss_secret_key', 'storage_oss_bucket', 'storage_oss_endpoint', 'storage_oss_cdn_domain', 'storage_qiniu_domains_default', 'storage_qiniu_domains_https', 'storage_qiniu_access_key', 'storage_qiniu_secret_key', 'storage_qiniu_bucket', 'storage_qiniu_notify_url'); $sp397a5c = array('storage_oss_is_ssl', 'storage_oss_is_cname'); if ($spfeab54->isMethod('GET')) { $spbe80b7 = array(); foreach ($spfb8dfb as $sp505b52) { $spbe80b7[$sp505b52] = \App\System::_get($sp505b52); } foreach ($sp397a5c as $sp505b52) { $spbe80b7[$sp505b52] = (int) \App\System::_get($sp505b52); } return Response::success($spbe80b7); } $this->set($spfeab54, $spfb8dfb); $this->set($spfeab54, $sp397a5c); return Response::success(); } function emailTest(Request $spfeab54) { $this->validate($spfeab54, array('to' => 'required')); $sp4af8fe = $spfeab54->post('to'); try { $spb9589c = Mail::to($sp4af8fe)->send(new \App\Mail\Test()); return Response::success($spb9589c); } catch (\Throwable $sp81eee8) { \App\Library\LogHelper::setLogFile('mail'); \Log::error('Mail Test Exception:' . $sp81eee8->getMessage()); return Response::fail($sp81eee8->getMessage(), $sp81eee8); } } function orderClean(Request $spfeab54) { $this->validate($spfeab54, array('day' => 'required|integer|min:1')); $sp842b0e = (int) $spfeab54->post('day'); \App\Order::where('status', \App\Order::STATUS_UNPAY)->where('created_at', '<', (new \Carbon\Carbon())->addDays(-$sp842b0e))->delete(); return Response::success(); } }
<?php
namespace App\Library\Pay; class Pay { private $driver = null; public function goPay($spdc9a36, $sp7c88f3, $sp45f07e, $sp873488, $sp429fcc) { $this->driver = static::getDriver($spdc9a36->id, $spdc9a36->driver); $spbe80b7 = json_decode($spdc9a36->config, true); $spbe80b7['payway'] = $spdc9a36->way; $this->driver->goPay($spbe80b7, $sp7c88f3, $sp45f07e, $sp873488, $sp429fcc); return true; } public static function getDriver($sp15a8b5, $sp33ee9e) { $sp892626 = 'App\\Library\\Pay\\' . ucfirst($sp33ee9e) . '\\Api'; if (!class_exists($sp892626)) { throw new \Exception('支付驱动未找到'); } return new $sp892626($sp15a8b5); } }
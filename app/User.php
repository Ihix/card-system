<?php
namespace App; use Carbon\Carbon; use Illuminate\Notifications\Notifiable; use Illuminate\Foundation\Auth\User as Authenticatable; class User extends Authenticatable { use Notifiable; protected $guarded = array(); protected $hidden = array('password', 'remember_token'); protected $appends = array('m_balance', 'role'); protected $casts = array('theme_config' => 'array'); const ID_CUSTOMER = -1; const INVENTORY_RANGE = 0; const INVENTORY_REAL = 1; const INVENTORY_AUTO = 2; const FEE_TYPE_MERCHANT = 0; const FEE_TYPE_CUSTOMER = 1; const FEE_TYPE_AUTO = 2; const STATUS_OK = 0; const STATUS_FROZEN = 1; function getMBalanceAttribute() { return $this->m_all - $this->m_paid - $this->m_frozen; } function getRoleAttribute() { return 'admin'; } function getMBalanceWithoutTodayAttribute() { $sp645757 = (int) \App\Order::where('user_id', $this->user_id)->where('status', \App\Order::STATUS_SUCCESS)->whereDate('paid_at', Carbon::today())->sum('income'); return $this->m_all - $this->m_paid - $this->m_frozen - $sp645757; } function getShopThemeAttribute() { if ($this->theme_config) { $sp6a22d6 = \App\ShopTheme::whereName($this->theme_config['theme'])->first(); if ($sp6a22d6) { return $sp6a22d6; } } return \App\ShopTheme::defaultTheme(); } function categories() { return $this->hasMany(Category::class); } function products() { return $this->hasMany(Product::class); } function cards() { return $this->hasMany(Card::class); } function orders() { return $this->hasMany(Order::class); } function coupons() { return $this->hasMany(Coupon::class); } function logs() { return $this->hasMany(Log::class); } function shop_theme() { return $this->belongsTo(ShopTheme::class); } }
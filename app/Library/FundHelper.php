<?php
namespace App\Library; use App\Order; use App\User; use App\FundRecord; use Illuminate\Support\Facades\DB; class FundHelper { const ACTION_CONTINUE = 1001; public static function orderSuccess($sp48ee76, callable $sp8b6933) { $sp4f4c0d = null; try { return DB::transaction(function () use($sp48ee76, &$sp4f4c0d, $sp8b6933) { $sp4f4c0d = \App\Order::where('id', $sp48ee76)->lockForUpdate()->firstOrFail(); $spb9589c = $sp8b6933($sp4f4c0d); if ($spb9589c !== self::ACTION_CONTINUE) { return $spb9589c; } $spafa70b = User::where('id', $sp4f4c0d->user_id)->lockForUpdate()->firstOrFail(); $spafa70b->m_all += $sp4f4c0d->income; $spafa70b->saveOrFail(); $sp1d0214 = new FundRecord(); $sp1d0214->user_id = $sp4f4c0d->user_id; $sp1d0214->type = FundRecord::TYPE_IN; $sp1d0214->amount = $sp4f4c0d->income; $sp1d0214->all = $spafa70b->m_all; $sp1d0214->frozen = $spafa70b->m_frozen; $sp1d0214->paid = $spafa70b->m_paid; $sp1d0214->balance = $spafa70b->m_balance; $sp1d0214->remark = '订单#' . $sp4f4c0d->order_no; $sp1d0214->order_id = $sp4f4c0d->id; $sp1d0214->saveOrFail(); return true; }); } catch (\Throwable $sp81eee8) { $sp093e23 = 'FundHelper.orderSuccess error, order_id:' . $sp48ee76; if ($sp4f4c0d) { $sp093e23 .= ', user_id:' . $sp4f4c0d->user_id . ',income:' . $sp4f4c0d->income . ',order_no:' . $sp4f4c0d->order_no; } Log::error($sp093e23 . ' with exception:', array('Exception' => $sp81eee8)); return false; } } public static function orderFreeze($sp48ee76, $spe3efa4) { $sp4f4c0d = null; try { return DB::transaction(function () use($sp48ee76, &$sp4f4c0d, $spe3efa4) { $sp4f4c0d = \App\Order::where('id', $sp48ee76)->lockForUpdate()->firstOrFail(); if ($sp4f4c0d->status === Order::STATUS_REFUND) { return false; } if ($sp4f4c0d->status === Order::STATUS_FROZEN) { return true; } $sp278862 = $sp4f4c0d->status; if ($sp278862 === \App\Order::STATUS_SUCCESS) { $spa88bbc = '已发货'; } elseif ($sp278862 === \App\Order::STATUS_UNPAY) { $spa88bbc = '未付款'; } elseif ($sp278862 === \App\Order::STATUS_PAID) { $spa88bbc = '未发货'; } else { throw new \Exception('unknown'); } $spafa70b = User::where('id', $sp4f4c0d->user_id)->lockForUpdate()->firstOrFail(); $sp1d0214 = new FundRecord(); $sp1d0214->user_id = $sp4f4c0d->user_id; $sp1d0214->type = FundRecord::TYPE_OUT; $sp1d0214->order_id = $sp4f4c0d->id; $sp1d0214->remark = $sp4f4c0d === $sp4f4c0d ? '' : '关联订单#' . $sp4f4c0d->order_no . ': '; if ($sp278862 === \App\Order::STATUS_SUCCESS) { $spafa70b->m_frozen += $sp4f4c0d->income; $spafa70b->saveOrFail(); $sp1d0214->amount = -$sp4f4c0d->income; $sp1d0214->remark .= $spe3efa4 . ', 冻结订单#' . $sp4f4c0d->order_no; } else { $sp1d0214->amount = 0; $sp1d0214->remark .= $spe3efa4 . ', 冻结订单(' . $spa88bbc . ')#' . $sp4f4c0d->order_no; } $sp1d0214->all = $spafa70b->m_all; $sp1d0214->frozen = $spafa70b->m_frozen; $sp1d0214->paid = $spafa70b->m_paid; $sp1d0214->balance = $spafa70b->m_balance; $sp1d0214->saveOrFail(); $sp4f4c0d->status = \App\Order::STATUS_FROZEN; $sp4f4c0d->frozen_reason = ($sp4f4c0d === $sp4f4c0d ? '' : '关联订单#' . $sp4f4c0d->order_no . ': ') . $spe3efa4; $sp4f4c0d->saveOrFail(); return true; }); } catch (\Throwable $sp81eee8) { $sp093e23 = 'FundHelper.orderFreeze error'; if ($sp4f4c0d) { $sp093e23 .= ', order_no:' . $sp4f4c0d->order_no . ', user_id:' . $sp4f4c0d->user_id . ', amount:' . $sp4f4c0d->income; } else { $sp093e23 .= ', order_no: null'; } Log::error($sp093e23 . ' with exception:', array('Exception' => $sp81eee8)); return false; } } public static function orderUnfreeze($sp48ee76, $sp114fde, callable $spab60c4 = null, &$sp56845b = null) { $sp4f4c0d = null; try { return DB::transaction(function () use($sp48ee76, &$sp4f4c0d, $sp114fde, $spab60c4, &$sp56845b) { $sp4f4c0d = \App\Order::where('id', $sp48ee76)->lockForUpdate()->firstOrFail(); if ($spab60c4 !== null) { $spb9589c = $spab60c4(); if ($spb9589c !== self::ACTION_CONTINUE) { return $spb9589c; } } if ($sp4f4c0d->status === Order::STATUS_REFUND) { $sp56845b = $sp4f4c0d->status; return false; } if ($sp4f4c0d->status !== Order::STATUS_FROZEN) { $sp56845b = $sp4f4c0d->status; return true; } $sp34c679 = $sp4f4c0d->card_orders()->exists(); if ($sp34c679) { $sp56845b = \App\Order::STATUS_SUCCESS; $spa88bbc = '已发货'; } else { if ($sp4f4c0d->paid_at === NULL) { $sp56845b = \App\Order::STATUS_UNPAY; $spa88bbc = '未付款'; } else { $sp56845b = \App\Order::STATUS_PAID; $spa88bbc = '未发货'; } } $spafa70b = User::where('id', $sp4f4c0d->user_id)->lockForUpdate()->firstOrFail(); $sp1d0214 = new FundRecord(); $sp1d0214->user_id = $sp4f4c0d->user_id; $sp1d0214->type = FundRecord::TYPE_IN; $sp1d0214->remark = $sp4f4c0d === $sp4f4c0d ? '' : '关联订单#' . $sp4f4c0d->order_no . ': '; $sp1d0214->order_id = $sp4f4c0d->id; if ($sp34c679) { $spafa70b->m_frozen -= $sp4f4c0d->income; $spafa70b->saveOrFail(); $sp1d0214->amount = $sp4f4c0d->income; $sp1d0214->remark .= $sp114fde . ', 解冻订单#' . $sp4f4c0d->order_no; } else { $sp1d0214->amount = 0; $sp1d0214->remark .= $sp114fde . ', 解冻订单(' . $spa88bbc . ')#' . $sp4f4c0d->order_no; } $sp1d0214->all = $spafa70b->m_all; $sp1d0214->frozen = $spafa70b->m_frozen; $sp1d0214->paid = $spafa70b->m_paid; $sp1d0214->balance = $spafa70b->m_balance; $sp1d0214->saveOrFail(); $sp4f4c0d->status = $sp56845b; $sp4f4c0d->saveOrFail(); return true; }); } catch (\Throwable $sp81eee8) { $sp093e23 = 'FundHelper.orderUnfreeze error'; if ($sp4f4c0d) { $sp093e23 .= ', order_no:' . $sp4f4c0d->order_no . ', user_id:' . $sp4f4c0d->user_id . ',amount:' . $sp4f4c0d->income; } else { $sp093e23 .= ', order_no: null'; } Log::error($sp093e23 . ' with exception:', array('Exception' => $sp81eee8)); return false; } } }
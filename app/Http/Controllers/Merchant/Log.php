<?php
namespace App\Http\Controllers\Merchant; use App\Library\Response; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Auth; class Log extends Controller { function get(Request $spfeab54) { $spfa021e = $spfeab54->post('user_id'); $spce52fe = $spfeab54->post('action', \App\Log::ACTION_LOGIN); $sp5786ca = \App\Log::where('action', $spce52fe); $sp5786ca->where('user_id', Auth::id()); $spd3b73a = $spfeab54->post('start_at'); if (strlen($spd3b73a)) { $sp5786ca->where('created_at', '>=', $spd3b73a . ' 00:00:00'); } $sp694c55 = $spfeab54->post('end_at'); if (strlen($sp694c55)) { $sp5786ca->where('created_at', '<=', $sp694c55 . ' 23:59:59'); } $spd5ff21 = $spfeab54->post('current_page', 1); $sp372a98 = $spfeab54->post('per_page', 20); $spaff91c = $sp5786ca->orderBy('created_at', 'DESC')->paginate($sp372a98, array('*'), 'page', $spd5ff21); return Response::success($spaff91c); } }
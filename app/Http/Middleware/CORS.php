<?php
namespace App\Http\Middleware; use Closure; class CORS { public function handle($spfeab54, Closure $spdb228e) { if (config('app.debug')) { $sp28070b = array('Access-Control-Allow-Origin' => $spfeab54->header('Origin'), 'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE', 'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, Cookie, X-Requested-With, X-XSRF-TOKEN', 'Access-Control-Allow-Credentials' => 'true'); if ($spfeab54->isMethod('OPTIONS')) { return response()->make('', 200, $sp28070b); } $sp3ac4e3 = $spdb228e($spfeab54); foreach ($sp28070b as $sp1ed429 => $spb914e6) { $sp3ac4e3->headers->set($sp1ed429, $spb914e6); } return $sp3ac4e3; } return $spdb228e($spfeab54); } }
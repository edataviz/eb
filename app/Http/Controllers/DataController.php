<?php

namespace App\Http\Controllers;

class DataController extends EBController {
    public function getAllObjectsInfo($request) {
        $user = auth()->user();
        if(!$user) return null;

        $facility = $user->getScopeFacility(true);

        $workspace = App\Models\UserWorkspace::where('USER_NAME', '=', $user->NAME)->select('*')->first();

        $

        $config = [];
		return view ( 'eb.flow', ['config' => $config]);
    }
}

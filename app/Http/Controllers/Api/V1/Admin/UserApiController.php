<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserApiController extends Controller
{
    public function fetchUser(Request $request)
    {
        try {
            $usersdata = User::where('mobile',$request->mobile)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function indexAction() {
        $calendars = Calendar::with('events')->get();

        $users = User::with('calendars')->get();
        foreach ($users as $user) {
            $user->ownCalendarsCount = count($user->calendars);
            $user->publicCalendarsCount = count($user->calendars);
        }

        return view('admin', [
            'calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE),
            'users' => json_encode($users, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setUserRoleAction(Request  $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required',
            'role' => 'required'
        ]);
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'code' => 404,
                'data' => [
                    'message' => 'User not found...'
                ]

            ]);
        }
        $user->role = $request->role == 'admin' ? 'admin' : NULL;
        $user->save();
        return response()->json([
            'code' => 1,
            'data' => [
                'message' => 'User role updated successfully'
            ]
        ]);
    }
}

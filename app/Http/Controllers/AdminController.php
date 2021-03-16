<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public function indexAction() {
        $calendars = Calendar::with('events')->get();

        $users = User::with('calendars')->get();

        return view('admin', [
            'calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE),
            'users' => json_encode($users, JSON_UNESCAPED_UNICODE)
        ]);
    }
    public function usersAction() {
        echo "Users";
    }
}

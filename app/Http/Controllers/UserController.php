<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function index() {
        $user = \Auth::user();
        if (!$user || $user->rank < 2) {
            \App::abort(403);
        }

        $users = User::where('rank', '<', 2)->get();
        
        return view('users/index', [
            'users' => $users,
        ]);
    }
//
    public function update(Request $request) {
        $user = \Auth::user();
        if (!$user || $user->rank < 2) {
            \App::abort(403);
        }

        $users = User::where('rank', '<', 2)->get();
        foreach ($users as $user) {
            if ($request->input('user'.$user->id)) {
                $user->rank = 1;
            } else {
                $user->rank = 0;
            }
            $user->save();
        }

        return back()->with('status', [
            'success' => 'User permissions updated'
        ]);
    }
}

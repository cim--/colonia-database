<?php

namespace App\Http\Composers;

use Illuminate\View\View;


class AuthComposer
{
    public function compose(View $view)
    {
        $user = \Auth::User();
        $view->with('userrank', $user ? $user->rank : 0);
    }
}
<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

    }

    public function confirmation(Request $request)
    {
        $class = 'user';

        if ($request->has('hash')) {
            $user = User::where('hash_id', $request->offsetGet('hash'))->first();

            if ($user) {
                $confirmed = 1;
                $user->active = true;

                try {
                    $user->save();
                    $class = 'index';

                    return redirect()->guest(route('login', compact('confirmed')));

                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            } else {
                return view('confirmError', compact('class'));
            }
        }
    }
}

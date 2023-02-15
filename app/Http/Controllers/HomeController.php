<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Pantry\Bookmark;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications;
//        $notifications = $user->notifications;

        $bookmarks = Bookmark::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(5)->get();

        $notifications->markAsRead();

        return view('home', ['notifications' => $notifications, 'bookmarks' => $bookmarks]);
    }
}

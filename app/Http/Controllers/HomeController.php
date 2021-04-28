<?php

namespace App\Http\Controllers;

use App\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $bookmarks = Bookmark::paginate(100);

        $notifications->markAsRead();

        return view('home', ['notifications' => $notifications, 'bookmarks' => $bookmarks]);
    }
}

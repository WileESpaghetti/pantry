<?php

namespace App\Http\Controllers;

use HtmlBookmarks\Services\UploadService;
use Illuminate\Support\Facades\Auth;
use Pantry\Models\Bookmark;
use function HtmlBookmarks\Services\humanBytes;

class HomeController extends Controller
{

    private UploadService $uploadService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UploadService $uploadService)
    {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
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

        return view('home', ['notifications' => $notifications, 'bookmarks' => $bookmarks, 'uploadLimit' => humanBytes($this->uploadService->getUploadLimit())]);
    }
}

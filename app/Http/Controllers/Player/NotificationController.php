<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications;
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }

        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'view_link' => 'nullable|url',
            'type' => 'nullable|string|max:50',
        ]);

        $notification = Notification::create([
            'user_id' => $request->user_id ?? null,
            'title' => $request->title,
            'description' => $request->description ?? null,
            'type' => $request->type,
        ]);

        $notification->update([
            'view_link' => route('notifications.show', ['id' => $notification->id]),
        ]);

        return response()->json(['message' => 'Notification created successfully', 'data' => $notification], 201);


    }

    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "filter" => "nullable|string"
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validate->errors()
            ], 422);
        }

        try {
            $userId = $this->getAuthID($request);
            $filter = $request->filter;

            $notificationsQuery = Notification::query();


            if ($filter) {
                $notificationsQuery->where('type', $filter);
            }

            $notificationsQuery->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            });


            $notifications = $notificationsQuery->latest()->paginate(10);


            $notifications = NotificationResource::collection($notifications);

            return response()->json([
                'message' => 'Notifications fetched successfully',
                'data' => $notifications
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }


    public function show(Request $request, $id)
    {
        try {

            $userId = $this->getAuthID($request);

            $notification = Notification::where('id', $id)->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })->first();

            if (!$notification) {
                abort(404, 'Notification not found');
            }

            


            if ($notification->viewed == 0) {
                $notification->update(['viewed' => 1]);
            }

            $notification = new NotificationResource($notification);

            return response()->json(['message' => 'notifications fecthed succesfully', 'data' => $notification], 200);



        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }

    }





}
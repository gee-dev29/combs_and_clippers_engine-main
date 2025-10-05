<?php

namespace App\Http\Controllers\App;

use App\Http\Resources\MessageResource;
use App\Http\Resources\ThreadResource;
use App\Models\Message;
use App\Models\Thread;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Exception;

class MessageController extends Controller
{
  public function sendMessage(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'message' => 'required|string',
      'attachment' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
      'receiver_id' => 'required|integer',
      'thread_id' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user_id = $this->getAuthID($request);

      if ($request->filled('thread_id')) {
        $thread = Thread::find($request['thread_id']);
        if (!is_null($thread)) {
          $thread->update(['last_message_at' => date("Y-m-d H:i:s")]);
        } else {
          $thread = Thread::create(
            [
              'user_id' => $user_id,
              'last_message_at' => date("Y-m-d H:i:s"),
              'recipient' => $request->receiver_id
            ]
          );
        }
      } else {
        $thread = Thread::create(
          [
            'user_id' => $user_id,
            'last_message_at' => date("Y-m-d H:i:s"),
            'recipient' => $request->receiver_id
          ]
        );
      }
      $chat = Message::create([
        'thread_id' => $thread->id,
        'sender_id' => $user_id,
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
      ]);

      if ($request->hasFile('attachment')) {
        $imageArray = $this->imageUtil->saveImgArray($request->file('attachment'), '/attachments/', $chat->id, []);
        if (!is_null($imageArray)) {
          $attachment = array_shift($imageArray);
          $chat->update(['attachment' => $attachment]);
        }
      }

      $chats = Message::where('thread_id', $thread->id)->paginate($this->perPage);

      $chats = $this->addMeta(MessageResource::collection($chats));

      return response()->json(compact('chats'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getThreadMessages(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'thread_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $thread = Thread::find($request->thread_id);
      $thread = new ThreadResource($thread);
      return response()->json(compact('thread'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function getInbox(Request $request)
  {
    try {
      $user_id = $this->getAuthID($request);
      $threads = Thread::where('recipient', $user_id)->get();
      $threads = $this->addMeta(ThreadResource::collection($threads));
      return response()->json(compact('threads'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getOutbox(Request $request)
  {
    try {
      $user_id = $this->getAuthID($request);
      $threads = Thread::where('user_id', $user_id)->get();
      $threads = $this->addMeta(ThreadResource::collection($threads));
      return response()->json(compact('threads'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getAllMessages(Request $request)
  {
    try {
      $user_id = $this->getAuthID($request);
      $threads = Thread::where('user_id', $user_id)->orWhere('recipient', $user_id)->get();
      $threads = $this->addMeta(ThreadResource::collection($threads));
      return response()->json(compact('threads'), 201);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
}

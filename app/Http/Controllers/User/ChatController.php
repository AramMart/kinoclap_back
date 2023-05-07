<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }

    public function sendMessage() {
       try {
           $message = request()->input('message');
           $senderId = auth()->user()->id;
           $receiverIdInput = request()->input('receiver_id');

           if (!$message || !$receiverIdInput) {
               return response()->json([], 400);
           }

           Chat::create([
               'sender_id' => $senderId,
               'receiver_id' => $receiverIdInput,
               'message' => $message
           ]);

           return response()->json();

       } catch (\Exception $exception) {
           return response()->json([], 400);
       }

    }

    public function getMessages($receiverId) {
        $senderId = auth()->user()->id;

        $messages = Chat::where('sender_id', $senderId)->where('receiver_id', $receiverId)
            ->orderBy('created_at', 'DESC')
            ->paginate(300);

        Chat::where('sender_id', $senderId)->where('receiver_id', $receiverId)->update(['seen' => true]);

        return response()->json($messages);
    }

    public function getUnseenBadge() {
        $senderId = auth()->user()->id;
        $unseenMessage = Chat::where('sender_id', $senderId)->where('seen', false)->first();
        $badge = false;

        if ($unseenMessage) {
            $badge = true;
        }

        return response()->json(['unseen_messages' => $badge]);
    }

    public function getAllChatConversations() {
        $userId = auth()->user()->id;
        $users = User::whereHas('chatConversationForUser',function($query) use ($userId) {
            $query->where('sender_id' , $userId );
        })->with(['chatConversationForUser' => function($query)  {
            $query->orderBy('created_at', 'DESC')->first();
        }])->get()->sortBy('chat_conversation_for_user.created_at');

        return response()->json($users);
    }

}

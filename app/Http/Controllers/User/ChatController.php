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

        $messages = Chat::where('sender_id', $senderId)->where('receiver_id', $receiverId)->orWhere('receiver_id', $senderId)->where('sender_id', $receiverId)
            ->orderBy('created_at', 'DESC')
            ->paginate(300);

        Chat::where('sender_id', $receiverId)->where('receiver_id', $senderId)->update(['seen' => true]);

        return response()->json($messages);
    }

    public function getUnseenBadge() {
        $unseenMessage = Chat::where('receiver_id',  auth()->user()->id)->where('seen', false)->first();
        $badge = false;

        if ($unseenMessage) {
            $badge = true;
        }

        return response()->json(['unseen_messages' => $badge]);
    }

    public function getAllChatConversations() {
        $userId = auth()->user()->id;

        $users = User::whereHas('chatConversationAsReceiver',function($query) use ($userId) {
            $query->where('receiver_id' , $userId )->orWhere('sender_id' , $userId );
        })->with(['chatConversationAsReceiver' => function($query)  {
            $query->with(['sender', 'receiver'])->orderBy('created_at', 'DESC')->first();
        }])->get()->sortBy('chatConversationAsReceiver.created_at');

        $conversations = [];

        foreach ($users as $user) {
            $chat = $user->chatConversationAsReceiver;
            if (count($chat)) {
                $chat = $chat[0];
                $conversationUser = $chat->receiver->id === $userId ? $chat->sender : $chat->receiver;
                array_push($conversations, ['date' => $chat->created_at, 'user' => $conversationUser]);
            }
        }

        return response()->json($conversations);
    }

}

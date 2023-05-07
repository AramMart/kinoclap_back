<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
class MailSender
{
    public static function sendNotificationForNewModeration($type) {
        Mail::send(
            'moderation.new_moderation',
            ['type' => $type],
            function ($message) {
                $message->to('aram.mart.2000@gmail.com', 'ADMIN')
                    ->subject(env('MAIL_MODERATION_SUBJECT'));
            }
        );
    }

    public static function sendRegistrationVerificationMail(User $user, $email_verification_token) {
        Mail::send(
            'auth.welcome_email',
            ['first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'welcome_to' => env('MAIL_FROM_NAME'),
                'url' => env('FRONT_URL').'/'.env('FRONT_ACCEPT_EMAIL').'/'.$email_verification_token
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->first_name)
                    ->subject(env('MAIL_REGISTRATION_SUBJECT'));
            }
        );
    }
}

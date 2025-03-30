<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPaymentStatus
{
    /**
     * Handle an incoming request.
     */
   public function handle(Request $request, Closure $next)
   {
       $user = Auth::user(); // Get the authenticated user

       if ($user && $user->type === 'user' && !$user->hasPaid()) {
           return response()->json(['message' => 'Access denied. Payment required.'], 402);
       }

       return $next($request); // Proceed if payment is valid or user is not logged in/admin
   }
}

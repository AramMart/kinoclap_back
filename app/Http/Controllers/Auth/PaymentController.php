<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Handle the payment callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paymentCallback(Request $request)
    {
        // Validate the incoming request parameters
        $validator = Validator::make($request->all(), [
            'EDP_PAYER_ACCOUNT' => 'required|integer|exists:users,id', // Ensure user exists
            'EDP_AMOUNT' => 'required|numeric|min:0', // Validate amount
            'EDP_TRANS_ID' => 'required|string|max:255', // Validate transaction ID
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Retrieve the POST parameters
        $payerAccount = $request->input('EDP_PAYER_ACCOUNT'); // User ID
        $amount = $request->input('EDP_AMOUNT'); // Payment amount
        $transactionId = $request->input('EDP_TRANS_ID'); // Transaction ID

        // Log incoming request for debugging
        Log::info("Payment callback received for user ID: $payerAccount, transaction ID: $transactionId, amount: $amount");

        // Find the user based on the payer account (user ID)
        $user = User::find($payerAccount);

        if ($user) {
            // Update the payment date and amount for the user
            $user->payment_date = now(); // Set the current date and time
            $user->payment_amount = $amount; // Save the payment amount
            $user->save();

            // Log the successful payment update
            Log::info("Payment confirmed for user ID: $payerAccount, transaction ID: $transactionId, amount: $amount");

            return response('Payment confirmed', 200); // Return success response
        } else {
            // Log the error if user is not found
            Log::error("User not found for ID: $payerAccount");

            return response('User not found', 404); // If user doesn't exist
        }
    }
}

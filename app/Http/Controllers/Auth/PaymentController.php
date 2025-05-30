<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    public function paymentFail(Request $request)
    {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'EDP_PAYER_ACCOUNT' => 'required|integer|exists:users,id', // Ensure user exists
            'EDP_TRANS_ID' => 'required|string|max:255', // Validate transaction ID
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Retrieve the payer account (user ID) and transaction ID
        $payerAccount = $request->input('EDP_PAYER_ACCOUNT');
        $transactionId = $request->input('EDP_TRANS_ID');

        // Log the failed payment attempt
        Log::error("Payment failed for user ID: $payerAccount, transaction ID: $transactionId");

        return response()->json([
            'status' => 'failed',
            'message' => 'Payment could not be processed'
        ], 400);
    }


    public function paymentCheck(Request $request)
    {
      Log::info('Payment Check', [
                    'request' =>$request->all()
                ]);

        if ($request->input('EDP_PRECHECK')) {
                // Validate the request parameters
                $validator = Validator::make($request->all(), [
                    'EDP_BILL_NO' => 'required|integer|exists:users,id', // Ensure user exists
                ]);


                // If validation fails, return error response
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }

                // Retrieve the payer account (user ID)
                $payerAccount = $request->input('EDP_BILL_NO');

                // Find the user
                $user = User::find($payerAccount);

                Log::info("Check confirmed: $payerAccount");

                if ($user) {
                    Log::info("Check confirmed and user exists: $payerAccount");
                    return response('OK', 200);
                } else {
                    return response()->json(['error' => 'User not found'], 403);
                }
        } else if ($request->input('EDP_TRANS_ID') && $request->input('EDP_CHECKSUM')) {
            // Validate the incoming request parameters
                    $validator = Validator::make($request->all(), [
                        'EDP_BILL_NO' => 'required|integer|exists:users,id', // Ensure user exists
                    ]);

                    // If validation fails, return error response
                    if ($validator->fails()) {
                        return response()->json(['errors' => $validator->errors()], 400);
                    }

                    // Retrieve the POST parameters
                    $payerAccount = $request->input('EDP_BILL_NO'); // User ID
                    $amount = 4000; // Payment amount

                    // Log incoming request for debugging
                    Log::info("Payment callback received for user ID: $payerAccount");

                    // Find the user based on the payer account (user ID)
                    $user = User::find($payerAccount);

                    if ($user) {
                        Log::info("Payment callback received for user ID YUHUUU: $payerAccount");
                        // Update the payment date and amount for the user
                        $user->payment_date = now(); // Set the current date and time
                        $user->payment_amount = $amount; // Save the payment amount
                        $user->save();

                        // Log the successful payment update
                        Log::info("Payment confirmed for user ID: $payerAccount");

                        return response('OK', 200);

                      } else {
                        // Log the error if user is not found
                        Log::error("User not found for ID: $payerAccount");

                        return response('OK', 403);

                        }
        }

    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Mail\WithdrawalOTP;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class WalletController extends Controller
{
    public function initiateWithdrawal(Request $request)
    {
        // Validate the request
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Get the seller's wallet
        $wallet = Wallet::where('sellerId', $seller->sellerId)->first();

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found for this seller.',
            ], 404);
        }

        // Check if the seller has sufficient balance
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient funds in the wallet.',
            ], 400);
        }

        // Generate OTP
        $otp = Str::random(6);

        // Store OTP in cache for 10 minutes
        Cache::put('withdrawal_otp_' . $seller->sellerId, $otp, 600);

        // Send OTP to seller's email
        Mail::to($seller->email)->send(new WithdrawalOTP($otp));

        return response()->json([
            'message' => 'Withdrawal initiated. Please check your email for the OTP.',
            'withdrawal_id' => Str::uuid(), // Generate a unique ID for this withdrawal request
        ], 200);
    }

    public function confirmWithdrawal(Request $request)
    {
        // Validate the request
        $request->validate([
            'withdrawal_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'otp' => 'required|string|size:6',
        ]);

        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Verify OTP
        $cachedOTP = Cache::get('withdrawal_otp_' . $seller->sellerId);
        if (!$cachedOTP || $cachedOTP !== $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
            ], 400);
        }

        // Get the seller's wallet
        $wallet = Wallet::where('sellerId', $seller->sellerId)->first();

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found for this seller.',
            ], 404);
        }

        // Check if the seller has sufficient balance
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient funds in the wallet.',
            ], 400);
        }

        // Process the withdrawal
        $wallet->balance -= $request->amount;
        $wallet->save();

        // Clear the OTP from cache
        Cache::forget('withdrawal_otp_' . $seller->sellerId);

        // TODO: Implement the actual transfer of funds to the seller's bank account

        return response()->json([
            'message' => 'Withdrawal successful.',
            'new_balance' => $wallet->balance,
        ], 200);
    }

    public function getAllWithdrawals(Request $request)
    {
        $seller = $this->getAuthenticatedSeller();

        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $withdrawals = Withdrawal::where('seller_id', $seller->getId())
            ->where('seller_type', $seller->getType())
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($withdrawals, 200);
    }

    public function getAllWithdrawalRequests(Request $request)
    {
        $seller = $this->getAuthenticatedSeller();

        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $withdrawalRequests = Withdrawal::where('seller_id', $seller->getId())
            ->where('seller_type', $seller->getType())
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('initiated_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($withdrawalRequests, 200);
    }

    public function getWithdrawalDetails($withdrawalId)
    {
        $seller = $this->getAuthenticatedSeller();

        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)
            ->where('seller_id', $seller->getId())
            ->where('seller_type', $seller->getType())
            ->first();

        if (!$withdrawal) {
            return response()->json([
                'message' => 'Withdrawal not found.',
            ], 404);
        }

        return response()->json($withdrawal, 200);
    }

    private function getAuthenticatedSeller()
    {
        $user = Auth::user();
        
        $seller = Seller::where('sellerId', $user->sellerId)->first();
        if ($seller) {
            $seller->setType('individual');
            return $seller;
        }

        $companySeller = CompanySeller::where('companySellerId', $user->companySellerId)->first();
        if ($companySeller) {
            $companySeller->setType('company');
            return $companySeller;
        }

        return null;
    }

}
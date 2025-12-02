<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout session for ticket purchase.
     */
    public function createCheckoutSession(Request $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        // Check if lottery is active and accepting tickets
        if (!$lottery->canPurchaseTickets()) {
            return response()->json([
                'message' => 'This lottery is not currently accepting ticket purchases',
            ], 400);
        }

        // Check if user email is verified
        if (!$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email before purchasing tickets',
            ], 403);
        }

        // Check available tickets
        $availableTickets = $lottery->total_tickets - $lottery->tickets_sold;
        if ($validated['quantity'] > $availableTickets) {
            return response()->json([
                'message' => "Only {$availableTickets} tickets available",
            ], 400);
        }

        $quantity = $validated['quantity'];
        $unitPrice = (int) ($lottery->ticket_price * 100); // Convert to cents
        $totalAmount = $unitPrice * $quantity;

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "Lottery Tickets: {$lottery->title}",
                            'description' => "{$quantity} ticket(s) for lottery #{$lottery->id}",
                        ],
                        'unit_amount' => $unitPrice,
                    ],
                    'quantity' => $quantity,
                ]],
                'mode' => 'payment',
                'success_url' => config('app.frontend_url', url('/')) . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url', url('/')) . '/payment/cancel',
                'metadata' => [
                    'lottery_id' => $lottery->id,
                    'user_id' => $request->user()->id,
                    'quantity' => $quantity,
                ],
                'customer_email' => $request->user()->email,
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
                'lottery_id' => $lottery->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Failed to create checkout session',
            ], 500);
        }
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the checkout.session.completed event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->fulfillOrder($session);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Fulfill the order by creating tickets after successful payment.
     */
    protected function fulfillOrder($session): void
    {
        $lotteryId = $session->metadata->lottery_id ?? null;
        $userId = $session->metadata->user_id ?? null;
        $quantity = $session->metadata->quantity ?? null;

        if (!$lotteryId || !$userId || !$quantity) {
            Log::error('Stripe webhook missing metadata', [
                'session_id' => $session->id,
                'metadata' => $session->metadata,
            ]);
            return;
        }

        $lottery = Lottery::find($lotteryId);
        if (!$lottery) {
            Log::error('Stripe webhook lottery not found', ['lottery_id' => $lotteryId]);
            return;
        }

        // Create tickets with lock to prevent race conditions
        try {
            DB::transaction(function () use ($lottery, $userId, $quantity, $session) {
                // Lock the lottery row
                $lottery = Lottery::lockForUpdate()->find($lottery->id);

                $startNumber = $lottery->getNextTicketNumber();

                for ($i = 0; $i < $quantity; $i++) {
                    Ticket::create([
                        'lottery_id' => $lottery->id,
                        'user_id' => $userId,
                        'ticket_number' => $startNumber + $i,
                        'purchased_at' => now(),
                        'stripe_session_id' => $session->id,
                    ]);
                }
            });

            Log::info('Tickets created successfully', [
                'lottery_id' => $lotteryId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create tickets after payment', [
                'error' => $e->getMessage(),
                'lottery_id' => $lotteryId,
                'user_id' => $userId,
                'session_id' => $session->id,
            ]);
        }
    }

    /**
     * Verify a checkout session status.
     */
    public function verifySession(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json(['message' => 'Session ID required'], 400);
        }

        try {
            $session = Session::retrieve($sessionId);

            return response()->json([
                'status' => $session->payment_status,
                'lottery_id' => $session->metadata->lottery_id ?? null,
                'quantity' => $session->metadata->quantity ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify Stripe session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Failed to verify session'], 500);
        }
    }
}


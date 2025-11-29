<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Purchase tickets for a lottery.
     */
    public function purchase(Request $request, Lottery $lottery): JsonResponse
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

        // Purchase tickets with lock to prevent race conditions
        $tickets = DB::transaction(function () use ($lottery, $request, $validated) {
            // Lock the lottery row
            $lottery = Lottery::lockForUpdate()->find($lottery->id);

            $tickets = [];
            $startNumber = $lottery->getNextTicketNumber();

            for ($i = 0; $i < $validated['quantity']; $i++) {
                $tickets[] = Ticket::create([
                    'lottery_id' => $lottery->id,
                    'user_id' => $request->user()->id,
                    'ticket_number' => $startNumber + $i,
                    'purchased_at' => now(),
                ]);
            }

            return $tickets;
        });

        return response()->json([
            'message' => "Successfully purchased {$validated['quantity']} ticket(s)",
            'tickets' => $tickets,
        ], 201);
    }

    /**
     * Get current user's tickets.
     */
    public function userTickets(Request $request): JsonResponse
    {
        $tickets = $request->user()->tickets()
            ->with(['lottery' => function ($query) {
                $query->with(['images', 'winner:id,name']);
            }])
            ->orderByDesc('purchased_at')
            ->get();

        return response()->json($tickets);
    }
}


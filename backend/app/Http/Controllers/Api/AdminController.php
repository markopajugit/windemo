<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lottery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get admin statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'pending' => Lottery::where('status', 'pending')->count(),
            'active' => Lottery::where('status', 'active')->count(),
            'completed' => Lottery::where('status', 'completed')->count(),
            'total' => Lottery::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get all lotteries (admin view).
     */
    public function lotteries(Request $request): JsonResponse
    {
        $query = Lottery::with(['user:id,name', 'images']);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', "%{$request->search}%");
                    });
            });
        }

        // Limit (for dashboard quick view)
        if ($request->limit) {
            return response()->json($query->orderByDesc('created_at')->take($request->limit)->get());
        }

        $lotteries = $query->orderByDesc('created_at')->paginate(15);

        return response()->json($lotteries);
    }

    /**
     * Approve a lottery.
     */
    public function approve(Request $request, Lottery $lottery): JsonResponse
    {
        if ($lottery->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending lotteries can be approved',
            ], 400);
        }

        $oldValues = $lottery->toArray();

        $lottery->update([
            'status' => 'active',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // Create audit log
        AuditLog::log(
            'approve',
            $lottery,
            ['status' => $oldValues['status']],
            ['status' => 'active'],
            'Lottery approved'
        );

        return response()->json([
            'message' => 'Lottery approved successfully',
            'lottery' => $lottery->fresh(['user', 'images']),
        ]);
    }

    /**
     * Reject a lottery.
     */
    public function reject(Request $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($lottery->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending lotteries can be rejected',
            ], 400);
        }

        $oldValues = $lottery->toArray();

        $lottery->update([
            'status' => 'cancelled',
            'rejection_reason' => $validated['reason'],
        ]);

        // Create audit log
        AuditLog::log(
            'reject',
            $lottery,
            ['status' => $oldValues['status']],
            ['status' => 'cancelled', 'rejection_reason' => $validated['reason']],
            $validated['reason']
        );

        return response()->json([
            'message' => 'Lottery rejected',
            'lottery' => $lottery->fresh(['user', 'images']),
        ]);
    }

    /**
     * Update a lottery (admin).
     */
    public function update(Request $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'product_value' => ['sometimes', 'numeric', 'min:1'],
            'ticket_price' => ['sometimes', 'numeric', 'min:0.50'],
            'total_tickets' => ['sometimes', 'integer', 'min:10'],
            'charity_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after:starts_at'],
            'status' => ['sometimes', 'in:pending,approved,active,completed,cancelled'],
        ]);

        $oldValues = $lottery->only(array_keys($validated));

        $lottery->update($validated);

        // Create audit log
        AuditLog::log(
            'edit',
            $lottery,
            $oldValues,
            $validated,
            'Admin updated lottery'
        );

        return response()->json([
            'message' => 'Lottery updated successfully',
            'lottery' => $lottery->fresh(['user', 'images']),
        ]);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LotteryController extends Controller
{
    /**
     * Get all public lotteries (active/approved).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lottery::with(['user:id,name', 'images'])
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Sort
        switch ($request->sort) {
            case 'ending_soon':
                $query->orderBy('ends_at', 'asc');
                break;
            case 'popular':
                $query->withCount('tickets')->orderByDesc('tickets_count');
                break;
            case 'price_low':
                $query->orderBy('ticket_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('ticket_price', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $lotteries = $query->paginate(12);

        return response()->json($lotteries);
    }

    /**
     * Get popular lotteries.
     */
    public function popular(): JsonResponse
    {
        $lotteries = Lottery::with(['user:id,name', 'images'])
            ->active()
            ->popular()
            ->take(6)
            ->get();

        return response()->json($lotteries);
    }

    /**
     * Get upcoming lotteries (approved but not yet started).
     */
    public function upcoming(): JsonResponse
    {
        $lotteries = Lottery::with(['user:id,name', 'images'])
            ->where('status', 'active')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at', 'asc')
            ->take(12)
            ->get();

        return response()->json($lotteries);
    }

    /**
     * Get ended/completed lotteries with winners.
     */
    public function ended(): JsonResponse
    {
        $lotteries = Lottery::with(['user:id,name', 'images', 'winner:id,name'])
            ->where('status', 'completed')
            ->orderBy('ends_at', 'desc')
            ->take(12)
            ->get();

        return response()->json($lotteries);
    }

    /**
     * Get a single lottery.
     */
    public function show(Lottery $lottery): JsonResponse
    {
        $lottery->load(['user:id,name', 'images', 'winner:id,name']);

        return response()->json($lottery);
    }

    /**
     * Get current user's lotteries.
     */
    public function userLotteries(Request $request): JsonResponse
    {
        $lotteries = $request->user()->lotteries()
            ->with(['images'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($lotteries);
    }

    /**
     * Create a new lottery.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'product_value' => ['required', 'numeric', 'min:1', 'max:100000'],
            'ticket_price' => ['required', 'numeric', 'min:0.50', 'max:1000'],
            'total_tickets' => ['required', 'integer', 'min:10', 'max:10000'],
            'charity_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $lottery = DB::transaction(function () use ($validated, $request) {
            $lottery = Lottery::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'product_value' => $validated['product_value'],
                'ticket_price' => $validated['ticket_price'],
                'total_tickets' => $validated['total_tickets'],
                'charity_percentage' => $validated['charity_percentage'] ?? 0,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'status' => 'pending',
            ]);

            // Store images
            foreach ($request->file('images') as $index => $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('lottery-images', $filename, 'public');

                LotteryImage::create([
                    'lottery_id' => $lottery->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }

            return $lottery;
        });

        $lottery->load('images');

        return response()->json($lottery, 201);
    }

    /**
     * Update a lottery (only if pending).
     */
    public function update(Request $request, Lottery $lottery): JsonResponse
    {
        // Check ownership
        if ($lottery->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Can only edit pending lotteries
        if ($lottery->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot edit lottery that is no longer pending',
            ], 400);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'product_value' => ['required', 'numeric', 'min:1', 'max:100000'],
            'ticket_price' => ['required', 'numeric', 'min:0.50', 'max:1000'],
            'total_tickets' => ['required', 'integer', 'min:10', 'max:10000'],
            'charity_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'images' => ['nullable', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($lottery, $validated, $request) {
            $lottery->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'product_value' => $validated['product_value'],
                'ticket_price' => $validated['ticket_price'],
                'total_tickets' => $validated['total_tickets'],
                'charity_percentage' => $validated['charity_percentage'] ?? 0,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
            ]);

            // Replace images if new ones uploaded
            if ($request->hasFile('images')) {
                // Delete old images
                foreach ($lottery->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                // Store new images
                foreach ($request->file('images') as $index => $image) {
                    $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('lottery-images', $filename, 'public');

                    LotteryImage::create([
                        'lottery_id' => $lottery->id,
                        'image_path' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        $lottery->load('images');

        return response()->json($lottery);
    }

    /**
     * Delete a lottery (only if pending).
     */
    public function destroy(Request $request, Lottery $lottery): JsonResponse
    {
        // Check ownership
        if ($lottery->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Can only delete pending lotteries
        if ($lottery->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete lottery that is no longer pending',
            ], 400);
        }

        // Delete images
        foreach ($lottery->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $lottery->delete();

        return response()->json(['message' => 'Lottery deleted successfully']);
    }
}


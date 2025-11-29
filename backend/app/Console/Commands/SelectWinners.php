<?php

namespace App\Console\Commands;

use App\Mail\WinnerNotification;
use App\Models\Lottery;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SelectWinners extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lotteries:select-winners';

    /**
     * The console command description.
     */
    protected $description = 'Select winners for ended lotteries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for lotteries that need winner selection...');

        $lotteries = Lottery::needsWinnerSelection()->get();

        if ($lotteries->isEmpty()) {
            $this->info('No lotteries need winner selection.');
            return Command::SUCCESS;
        }

        foreach ($lotteries as $lottery) {
            $this->selectWinner($lottery);
        }

        $this->info("Processed {$lotteries->count()} lottery(ies).");

        return Command::SUCCESS;
    }

    /**
     * Select a winner for a lottery.
     */
    protected function selectWinner(Lottery $lottery): void
    {
        $this->info("Processing lottery: {$lottery->title} (ID: {$lottery->id})");

        $ticketCount = $lottery->tickets()->count();

        if ($ticketCount === 0) {
            $this->warn("  No tickets sold. Marking as cancelled.");
            $lottery->update([
                'status' => 'cancelled',
            ]);
            return;
        }

        // Use DB transaction with lock
        DB::transaction(function () use ($lottery) {
            // Lock the lottery
            $lottery = Lottery::lockForUpdate()->find($lottery->id);

            // Get all ticket IDs
            $ticketIds = $lottery->tickets()->pluck('id')->toArray();

            // Select random winner using cryptographically secure random
            $winnerTicketId = $ticketIds[random_int(0, count($ticketIds) - 1)];
            $winnerTicket = Ticket::find($winnerTicketId);

            // Update lottery
            $lottery->update([
                'status' => 'completed',
                'winner_user_id' => $winnerTicket->user_id,
            ]);

            $this->info("  Winner selected: User ID {$winnerTicket->user_id}, Ticket #{$winnerTicket->ticket_number}");

            // Log the winner selection
            Log::info("Lottery winner selected", [
                'lottery_id' => $lottery->id,
                'lottery_title' => $lottery->title,
                'winner_user_id' => $winnerTicket->user_id,
                'winner_ticket_id' => $winnerTicket->id,
                'winner_ticket_number' => $winnerTicket->ticket_number,
                'total_tickets' => count($ticketIds),
            ]);

            // Send email notification to winner
            try {
                Mail::to($winnerTicket->user)->send(new WinnerNotification($lottery, $winnerTicket));
                $this->info("  Winner notification email sent.");
            } catch (\Exception $e) {
                Log::error("Failed to send winner notification email", [
                    'lottery_id' => $lottery->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}


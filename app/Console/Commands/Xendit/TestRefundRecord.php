<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Payment\RefundController;

class TestRefundRecord extends Command
{
    protected $signature = 'xendit:test-refund-record {refundRequestId}';
    protected $description = 'Process a specific RefundRequest via RefundController for testing.';

    public function handle(): int
    {
        $id = (int) $this->argument('refundRequestId');
        $refund = RefundRequest::with(['ticketPurchase','subscription','additionalTicketPurchase','user'])->find($id);
        if (!$refund) {
            $this->error('RefundRequest not found: ' . $id);
            return self::FAILURE;
        }

        $this->info('Processing refund request #' . $refund->id . ' for user ' . optional($refund->user)->email);

        // Simulate an admin request
        $request = Request::create('/admin/refunds/' . $refund->id . '/process', 'POST', [
            'admin_notes' => 'Tester command run',
        ]);

        // Call controller method directly
        app()->make(RefundController::class)->processRefund($request, $refund);

        $this->info('Dispatch complete. Check logs and UI for results.');
        return self::SUCCESS;
    }
}



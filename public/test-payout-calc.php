<?php
/**
 * Payout Calculation Test
 * Test URL: https://yourdomain.com/test-payout-calc.php
 */

// Security check
if (!isset($_GET['test']) || $_GET['test'] !== 'true') {
    http_response_code(403);
    die('Access Denied - Add ?test=true to URL');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Payout Calculation Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; }
        .test-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td, table th { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #4CAF50; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payout Calculation Test</h1>
        
        <?php
        try {
            require __DIR__ . '/../vendor/autoload.php';
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            
            echo '<div class="test-section">';
            echo '<h2>Test 1: Database Query Test</h2>';
            
            // Test calculating payout for event 2
            $eventId = 2;
            $event = \App\Models\Event::find($eventId);
            
            if (!$event) {
                echo '<div class="fail">✗ Event #' . $eventId . ' not found in database</div>';
            } else {
                echo '<div class="pass">✓ Event found: ' . $event->title . '</div>';
                
                // Get paid purchases
                $paidPurchases = \App\Models\TicketPurchase::where('event_id', $eventId)
                    ->where('status', 'paid')
                    ->get();
                
                echo '<p>Total paid purchases: ' . $paidPurchases->count() . '</p>';
                
                // Calculate total amount (what customers paid)
                $totalAmount = $paidPurchases->sum('total_amount');
                echo '<p>Total Amount (from customers): ₱' . number_format($totalAmount, 2) . '</p>';
                
                // Calculate net amount (after Xendit fees)
                $netAmount = $paidPurchases->sum(function($purchase) {
                    return $purchase->net_amount ?? $purchase->total_amount;
                });
                echo '<p>Net Amount (after Xendit fees): ₱' . number_format($netAmount, 2) . '</p>';
                
                // Calculate Qricket fee (5%)
                $qricketFeePercentage = 5;
                $qricketFee = $totalAmount * ($qricketFeePercentage / 100);
                $finalPayout = $totalAmount - $qricketFee;
                
                echo '<p>Qricket Fee (' . $qricketFeePercentage . '%): ₱' . number_format($qricketFee, 2) . '</p>';
                echo '<p><strong>Final Payout: ₱' . number_format($finalPayout, 2) . '</strong></p>';
            }
            echo '</div>';
            
            // Show detailed breakdown
            echo '<div class="test-section">';
            echo '<h2>Test 2: Detailed Purchase Breakdown</h2>';
            
            if (isset($paidPurchases) && $paidPurchases->count() > 0) {
                echo '<table>';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>Purchase ID</th>';
                echo '<th>Quantity</th>';
                echo '<th>Unit Price</th>';
                echo '<th>Total Amount</th>';
                echo '<th>Transaction Fee</th>';
                echo '<th>VAT</th>';
                echo '<th>Net Amount</th>';
                echo '<th>Status</th>';
                echo '</tr>';
                
                foreach ($paidPurchases->take(10) as $purchase) {
                    echo '<tr>';
                    echo '<td>' . $purchase->id . '</td>';
                    echo '<td>' . substr($purchase->purchase_id, 0, 20) . '...</td>';
                    echo '<td>' . $purchase->quantity . '</td>';
                    echo '<td>₱' . number_format($purchase->unit_price, 2) . '</td>';
                    echo '<td>₱' . number_format($purchase->total_amount, 2) . '</td>';
                    echo '<td>₱' . number_format($purchase->transaction_fee ?? 0, 2) . '</td>';
                    echo '<td>₱' . number_format($purchase->vat_amount ?? 0, 2) . '</td>';
                    echo '<td>₱' . number_format($purchase->net_amount ?? 0, 2) . '</td>';
                    echo '<td>' . $purchase->status . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                if ($paidPurchases->count() > 10) {
                    echo '<p>... and ' . ($paidPurchases->count() - 10) . ' more purchases</p>';
                }
            }
            echo '</div>';
            
            // Test PayoutRequestController calculation
            echo '<div class="test-section">';
            echo '<h2>Test 3: Compare with PayoutRequestController Logic</h2>';
            
            $controllerTotal = \App\Models\TicketPurchase::where('event_id', $eventId)
                ->where('status', 'paid')
                ->sum('total_amount') ?? 0;
            
            $controllerNet = \App\Models\TicketPurchase::where('event_id', $eventId)
                ->where('status', 'paid')
                ->sum(\DB::raw('COALESCE(net_amount, total_amount)')) ?? 0;
            
            echo '<p>Using total_amount (gross): ₱' . number_format($controllerTotal, 2) . '</p>';
            echo '<p>Using net_amount (after fees): ₱' . number_format($controllerNet, 2) . '</p>';
            
            // Calculate with Qricket fee
            $qricketFeeFromTotal = $controllerTotal * 0.05;
            $finalPayoutFromTotal = $controllerTotal - $qricketFeeFromTotal;
            
            echo '<p><strong>Recommended Calculation (using total_amount):</strong></p>';
            echo '<ul>';
            echo '<li>Total Earnings: ₱' . number_format($controllerTotal, 2) . '</li>';
            echo '<li>Qricket Fee (5%): ₱' . number_format($qricketFeeFromTotal, 2) . '</li>';
            echo '<li>Net Payout: ₱' . number_format($finalPayoutFromTotal, 2) . '</li>';
            echo '</ul>';
            
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="fail">Error: ' . $e->getMessage() . '</div>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
        ?>
        
        <div class="test-section">
            <h2>Summary</h2>
            <p>This test shows:</p>
            <ul>
                <li>How many paid ticket purchases exist for the event</li>
                <li>The total amount customers paid (total_amount)</li>
                <li>The net amount after Xendit fees (net_amount)</li>
                <li>The correct payout calculation using total_amount with 5% Qricket fee</li>
            </ul>
            <p><strong>NOTE:</strong> The modal should show <strong>Total Earnings</strong> = total_amount (gross revenue from customers), then deduct 5% Qricket fee to get the final payout.</p>
        </div>
    </div>
</body>
</html>


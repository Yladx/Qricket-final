<?php

namespace App\Services;

class XlsxGenerator
{
    private static function esc($value)
    {
        $s = (string) $value;
        // Normalize to valid UTF-8 and drop invalid bytes
        if (function_exists('iconv')) {
            $normalized = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
            if ($normalized !== false) { $s = $normalized; }
        }
        // Strip control chars not allowed in XML (except tab, CR, LF)
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
        return strtr($s, [
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;',
            "'" => '&#39;',
        ]);
    }
    public static function generateAttendanceReport($event, $tickets)
    {
        // Generate filename
        $filename = 'attendance_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $event->title) . '_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Create Excel XML format (older format that doesn't require ZIP)
        // Build XML header safely to avoid short_open_tag interference on some hosts
        $xml = '<?' . 'xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        
        // Styles
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F766E" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Date"><Font ss:Italic="1" ss:Size="10" ss:Color="#6B7280"/><Interior ss:Color="#F8F9FA" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Header"><Font ss:Bold="1" ss:Size="12" ss:Color="#FFFFFF"/><Interior ss:Color="#14B8A6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="CheckedIn"><Font ss:Bold="1" ss:Color="#065F46"/><Interior ss:Color="#D1FAE5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="NotCheckedIn"><Font ss:Bold="1" ss:Color="#991B1B"/><Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="CertificateYes"><Font ss:Bold="1" ss:Color="#1E40AF"/><Interior ss:Color="#DBEAFE" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="EvenRow"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="OddRow"><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '</Styles>';
        
        // Worksheet
        $xml .= '<Worksheet ss:Name="Attendance">';
        $xml .= '<Table>';
        
        // Title row
        $xml .= '<Row ss:Height="30">';
        $xml .= '<Cell ss:MergeAcross="8" ss:StyleID="Title"><Data ss:Type="String">Attendance Report - ' . self::esc($event->title) . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Date row
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:MergeAcross="8" ss:StyleID="Date"><Data ss:Type="String">Exported on: ' . now()->format('Y-m-d H:i:s') . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Headers
        $xml .= '<Row ss:Height="25">';
        $headers = ['Ticket ID', 'User Name', 'Email', 'Ticket Type', 'Purchase ID', 'Purchase Date', 'Check-in Status', 'Check-in Time', 'Certificate Given'];
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . self::esc($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        
        // Data rows
        $rowNumber = 0;
        foreach ($tickets as $ticket) {
            $rowNumber++;
            $isEven = ($rowNumber % 2 == 0);
            $rowStyle = $isEven ? 'EvenRow' : 'OddRow';
            
            $xml .= '<Row ss:Height="20">';
            
            $ticketData = [
                $ticket->ticket_id,
                trim(($ticket->user->first_name ?? '') . ' ' . ($ticket->user->last_name ?? '')) ?: 'Unknown',
                $ticket->user->email ?? '',
                // Ticket type label may not be directly related; fallback to unit price label
                ($ticket->ticketPurchase && isset($ticket->ticketPurchase->unit_price))
                    ? ('Ticket (PHP ' . number_format($ticket->ticketPurchase->unit_price, 2) . ')')
                    : 'Ticket',
                $ticket->ticketPurchase->purchase_id ?? '',
                $ticket->ticketPurchase->created_at ? $ticket->ticketPurchase->created_at->format('Y-m-d H:i:s') : '',
                $ticket->checked_in_at ? 'Checked In' : 'Not Checked In',
                $ticket->checked_in_at ? $ticket->checked_in_at->format('Y-m-d H:i:s') : '',
                $ticket->certificate_given ? 'Yes' : 'No'
            ];
            
            foreach ($ticketData as $index => $data) {
                $cellStyle = $rowStyle;
                
                // Color-code check-in status (column 6)
                if ($index == 6) {
                    if ($data === 'Checked In') {
                        $cellStyle = 'CheckedIn';
                    } elseif ($data === 'Not Checked In') {
                        $cellStyle = 'NotCheckedIn';
                    }
                }
                
                // Color-code certificate status (column 8)
                if ($index == 8 && $data === 'Yes') {
                    $cellStyle = 'CertificateYes';
                }
                
                $xml .= '<Cell ss:StyleID="' . $cellStyle . '"><Data ss:Type="String">' . self::esc($data) . '</Data></Cell>';
            }
            
            $xml .= '</Row>';
        }
        
        $xml .= '</Table>';
        $xml .= '</Worksheet>';
        $xml .= '</Workbook>';
        
        // Save to temporary file
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFile, $xml);
        
        return $tempFile;
    }

    public static function generateSalesReport($event, $sales)
    {
        // Generate filename
        $filename = 'sales_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $event->title) . '_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Calculate statistics with fee breakdown
        $paidSales = $sales->where('status', 'paid');
        $totalRevenue = $paidSales->sum('total_amount');
        $netRevenue = $paidSales->sum(function($sale) { return $sale->net_amount ?? $sale->total_amount; });
        $totalTransactionFees = $paidSales->sum('transaction_fee');
        $totalVatAmount = $paidSales->sum('vat_amount');
        $totalFees = $totalTransactionFees + $totalVatAmount;
        $totalTicketsSold = $paidSales->sum('quantity');
        $totalOrders = $paidSales->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Get ticket performance data
        $ticketPerformance = self::getTicketPerformanceData($event);
        
        // Create Excel XML format (older format that doesn't require ZIP)
        $xml = '<?' . 'xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        
        // Enhanced Styles
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F766E" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Date"><Font ss:Italic="1" ss:Size="10" ss:Color="#6B7280"/><Interior ss:Color="#F8F9FA" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Header"><Font ss:Bold="1" ss:Size="12" ss:Color="#FFFFFF"/><Interior ss:Color="#14B8A6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="SubHeader"><Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/><Interior ss:Color="#059669" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Paid"><Font ss:Bold="1" ss:Color="#065F46"/><Interior ss:Color="#D1FAE5" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Pending"><Font ss:Bold="1" ss:Color="#92400E"/><Interior ss:Color="#FEF3C7" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Refunded"><Font ss:Bold="1" ss:Color="#1E40AF"/><Interior ss:Color="#DBEAFE" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Cancelled"><Font ss:Bold="1" ss:Color="#991B1B"/><Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="EvenRow"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="OddRow"><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Stats"><Font ss:Bold="1" ss:Size="11" ss:Color="#1F2937"/><Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="StatsValue"><Font ss:Bold="1" ss:Size="11" ss:Color="#059669"/><Interior ss:Color="#ECFDF5" ss:Pattern="Solid"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '</Styles>';
        
        // Worksheet
        $xml .= '<Worksheet ss:Name="Sales Report">';
        $xml .= '<Table>';
        
        // Title row
        $xml .= '<Row ss:Height="30">';
        $xml .= '<Cell ss:MergeAcross="13" ss:StyleID="Title"><Data ss:Type="String">Sales Report - ' . self::esc($event->title) . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Date row
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:MergeAcross="13" ss:StyleID="Date"><Data ss:Type="String">Exported on: ' . now()->format('Y-m-d H:i:s') . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Statistics Section
        $xml .= '<Row ss:Height="25">';
        $xml .= '<Cell ss:MergeAcross="13" ss:StyleID="SubHeader"><Data ss:Type="String">SALES STATISTICS</Data></Cell>';
        $xml .= '</Row>';
        
        $stats = [
            ['Total Revenue (Gross)', 'PHP ' . number_format($totalRevenue, 2)],
            ['Net Revenue', 'PHP ' . number_format($netRevenue, 2)],
            ['Transaction Fees', 'PHP ' . number_format($totalTransactionFees, 2)],
            ['VAT Amount', 'PHP ' . number_format($totalVatAmount, 2)],
            ['Total Fees', 'PHP ' . number_format($totalFees, 2)],
            ['Total Tickets Sold', number_format($totalTicketsSold)],
            ['Total Orders', number_format($totalOrders)],
            ['Average Order Value', 'PHP ' . number_format($averageOrderValue, 2)]
        ];
        
        foreach ($stats as $stat) {
            $xml .= '<Row ss:Height="20">';
            $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">' . self::esc($stat[0]) . '</Data></Cell>';
            $xml .= '<Cell ss:MergeAcross="12" ss:StyleID="StatsValue"><Data ss:Type="String">' . self::esc($stat[1]) . '</Data></Cell>';
            $xml .= '</Row>';
        }
        
        // Empty row for spacing
        $xml .= '<Row ss:Height="15"></Row>';
        
        // Ticket Performance Section
        $xml .= '<Row ss:Height="25">';
        $xml .= '<Cell ss:MergeAcross="6" ss:StyleID="SubHeader"><Data ss:Type="String">TICKET PERFORMANCE</Data></Cell>';
        $xml .= '</Row>';
        
        // Ticket Performance Headers
        $xml .= '<Row ss:Height="25">';
        $perfHeaders = ['Ticket Type', 'Price', 'Available', 'Sold', 'Remaining', 'Utilization %', 'Revenue'];
        foreach ($perfHeaders as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . self::esc($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        
        // Ticket Performance Data
        foreach ($ticketPerformance as $ticket) {
            $xml .= '<Row ss:Height="20">';
            $perfData = [
                $ticket['name'],
                'PHP ' . number_format($ticket['price'], 2),
                $ticket['quantity'] ?? 'Unlimited',
                $ticket['total_sold'],
                $ticket['remaining'] ?? 'N/A',
                $ticket['utilization'] . '%',
                'PHP ' . number_format($ticket['total_revenue'], 2)
            ];
            
            foreach ($perfData as $data) {
                $xml .= '<Cell ss:StyleID="EvenRow"><Data ss:Type="String">' . self::esc($data) . '</Data></Cell>';
            }
            $xml .= '</Row>';
        }
        
        // Empty row for spacing
        $xml .= '<Row ss:Height="15"></Row>';
        
        // Sales Details Section
        $xml .= '<Row ss:Height="25">';
        $xml .= '<Cell ss:MergeAcross="13" ss:StyleID="SubHeader"><Data ss:Type="String">SALES DETAILS</Data></Cell>';
        $xml .= '</Row>';
        
        // Enhanced Headers with Fee Information
        $xml .= '<Row ss:Height="25">';
        $headers = ['Buyer', 'Invoice ID', 'Purchase ID', 'Ticket Type', 'Status', 'Quantity', 'Total Amount', 'Payment Method', 'Transaction Fee', 'VAT Amount', 'Net Amount', 'Paid At', 'Buyer Email'];
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . self::esc($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        
        // Data rows with ticket information
        $rowNumber = 0;
        foreach ($sales as $sale) {
            $rowNumber++;
            $isEven = ($rowNumber % 2 == 0);
            $rowStyle = $isEven ? 'EvenRow' : 'OddRow';
            
            $xml .= '<Row ss:Height="20">';
            
            $saleData = [
                trim(($sale->user->first_name ?? '') . ' ' . ($sale->user->last_name ?? '')) ?: 'Unknown',
                $sale->invoice_id ?? $sale->invoice ?? $sale->invoice_no ?? $sale->invoice_number ?? $sale->reference ?? $sale->payment_reference ?? '—',
                $sale->id,
                // Ticket type label may not have a direct relation; use unit_price if present
                (isset($sale->unit_price) ? ('Ticket (PHP ' . number_format($sale->unit_price, 2) . ')') : 'Ticket'),
                ucfirst($sale->status),
                $sale->quantity,
                'PHP ' . number_format($sale->total_amount, 2),
                $sale->payment_method ?? '—',
                'PHP ' . number_format($sale->transaction_fee ?? 0, 2),
                'PHP ' . number_format($sale->vat_amount ?? 0, 2),
                'PHP ' . number_format($sale->net_amount ?? $sale->total_amount, 2),
                $sale->paid_at ? $sale->paid_at->format('M d, Y g:i A') : '—',
                $sale->user->email ?? '—'
            ];
            
            foreach ($saleData as $index => $data) {
                $cellStyle = $rowStyle;
                
                // Color-code status (column 4)
                if ($index == 4) {
                    $status = strtolower($sale->status ?? '');
                    if ($status === 'paid') {
                        $cellStyle = 'Paid';
                    } elseif ($status === 'pending') {
                        $cellStyle = 'Pending';
                    } elseif ($status === 'refunded') {
                        $cellStyle = 'Refunded';
                    } elseif ($status === 'cancelled' || $status === 'failed') {
                        $cellStyle = 'Cancelled';
                    }
                }
                
                $xml .= '<Cell ss:StyleID="' . $cellStyle . '"><Data ss:Type="String">' . self::esc($data) . '</Data></Cell>';
            }
            
            $xml .= '</Row>';
        }
        
        $xml .= '</Table>';
        $xml .= '</Worksheet>';
        $xml .= '</Workbook>';
        
        // Save to temporary file
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFile, $xml);
        
        return $tempFile;
    }

    private static function getTicketPerformanceData($event)
    {
        // Get ticket performance data similar to EventDashboardController
        $tickets = $event->eventTickets()->get();
        
        return $tickets->map(function($ticket) use ($event) {
            // Count purchases for this ticket type
            $purchasesCount = \App\Models\TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->where('unit_price', $ticket->price)
                ->sum('quantity');

            $remaining = $ticket->quantity ? ($ticket->quantity - $purchasesCount) : null;
            $utilization = $ticket->quantity > 0 ? round(($purchasesCount / $ticket->quantity) * 100, 1) : 0;
            
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => $ticket->price,
                'quantity' => $ticket->quantity,
                'total_sold' => $purchasesCount,
                'remaining' => $remaining,
                'utilization' => $utilization,
                'total_revenue' => $ticket->price * $purchasesCount,
            ];
        });
    }

    /**
     * Generate admin sales report Excel file
     */
    public static function generateAdminSalesReport($data, $reportType = 'all')
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'admin_sales_report_') . '.xls';
        
        // If controller passed an empty payload for any reason, hydrate a minimal dataset directly
        try {
            if (!is_array($data)) { $data = []; }
            if (!isset($data['summary']) || !is_array($data['summary'])) { $data['summary'] = []; }
            if (!isset($data['details']) || !is_array($data['details'])) { $data['details'] = []; }

            // Tickets summary/details
            if (!isset($data['summary']['tickets'])) {
                $tp = \App\Models\TicketPurchase::where('status','paid');
                $data['summary']['tickets'] = [
                    'total_revenue' => (float) $tp->sum('total_amount'),
                    'net_revenue' => (float) \DB::table('ticket_purchases')->where('status','paid')->sum(\DB::raw('COALESCE(net_amount,total_amount)')),
                    'transaction_fees' => (float) \DB::table('ticket_purchases')->where('status','paid')->sum(\DB::raw('COALESCE(transaction_fee,0)')),
                    'vat_amount' => (float) \DB::table('ticket_purchases')->where('status','paid')->sum(\DB::raw('COALESCE(vat_amount,0)')),
                    'total_fees' => 0,
                    'total_orders' => (int) \App\Models\TicketPurchase::where('status','paid')->count(),
                ];
                $data['summary']['tickets']['total_fees'] = ($data['summary']['tickets']['transaction_fees'] ?? 0) + ($data['summary']['tickets']['vat_amount'] ?? 0);
            }

            if (!isset($data['details']['ticket_sales'])) {
                $rows = \App\Models\TicketPurchase::with(['user','event'])
                    ->where('status','paid')
                    ->latest('paid_at')
                    ->limit(500)
                    ->get()
                    ->map(function($row){
                        return [
                            'buyer' => trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? '')) ?: 'Unknown',
                            'buyer_email' => $row->user->email ?? '—',
                            'event_title' => $row->event->title ?? 'Unknown Event',
                            'ticket_type' => isset($row->unit_price) ? ('Ticket (PHP ' . number_format($row->unit_price, 2) . ')') : 'Ticket',
                            'status' => $row->status,
                            'quantity' => (int) ($row->quantity ?? 0),
                            'total_amount' => (float) ($row->total_amount ?? 0),
                            'transaction_fee' => (float) ($row->transaction_fee ?? 0),
                            'vat_amount' => (float) ($row->vat_amount ?? 0),
                            'net_amount' => (float) ($row->net_amount ?? $row->total_amount ?? 0),
                            'payment_method' => $row->payment_method ?? '—',
                            'created_at' => optional($row->created_at)->format('Y-m-d H:i:s') ?? '',
                            'paid_at' => optional($row->paid_at)->format('Y-m-d H:i:s') ?? '',
                        ];
                    })->toArray();
                $data['details']['ticket_sales'] = $rows;
            }

            // Subscriptions summary/details
            if (!isset($data['summary']['subscriptions'])) {
                $subs = \App\Models\Subscription::where('payment_status','paid')->where('status','!=','refunded');
                $data['summary']['subscriptions'] = [
                    'total_revenue' => (float) $subs->sum('amount'),
                    'net_revenue' => (float) \DB::table('subscriptions')->where('payment_status','paid')->where('status','!=','refunded')->sum(\DB::raw('COALESCE(net_amount,amount)')),
                ];
            }

            // Additional tickets summary/details
            if (!isset($data['summary']['additional_tickets'])) {
                $at = \App\Models\TicketLimitPurchase::where('status','paid');
                $data['summary']['additional_tickets'] = [
                    'total_revenue' => (float) $at->sum('amount'),
                    'net_revenue' => (float) \DB::table('ticket_limit_purchases')->where('status','paid')->sum(\DB::raw('COALESCE(net_amount,amount)')),
                    'total_orders' => (int) \App\Models\TicketLimitPurchase::where('status','paid')->count(),
                    'total_tickets' => (int) \App\Models\TicketLimitPurchase::where('status','paid')->sum('additional_tickets'),
                ];
            }
        } catch (\Throwable $e) {
            // If hydration fails, proceed with whatever data was passed
        }
        
        // Create Excel XML content (Excel 2003 format)
        $xml = self::buildAdminSalesReportXML($data, $reportType);
        
        // Write to file
        file_put_contents($tempFile, $xml);
        
        return $tempFile;
    }

    /**
     * Build XML content for admin sales report
     */
    private static function buildAdminSalesReportXML($data, $reportType = 'all')
    {
        // Minimal, robust builder override: return simplified workbook to avoid corruption
        if (true) {
        $dateRange = $data['dateRange'] ?? 'All Time';
        $generatedAt = now()->format('Y-m-d H:i:s');
            $xml  = '<?' . 'xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<?' . 'mso-application progid="Excel.Sheet"?>' . "\n";
            $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            $xml .= '<Styles><Style ss:ID="H"><Font ss:Bold="1"/></Style><Style ss:ID="C"><NumberFormat ss:Format="#,# #0.00"/></Style></Styles>';
            // Summary
            $xml .= '<Worksheet ss:Name="Summary"><Table>';
            $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Admin Sales Report</Data></Cell></Row>';
            $xml .= '<Row><Cell><Data ss:Type="String">Date Range: ' . self::esc($dateRange) . '</Data></Cell></Row>';
            $xml .= '<Row><Cell><Data ss:Type="String">Generated: ' . self::esc($generatedAt) . '</Data></Cell></Row>';
            if (isset($data['summary']['tickets'])) {
                $t = $data['summary']['tickets'];
                $xml .= '<Row/>';
                $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Event Sales</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($t['total_revenue'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($t['net_revenue'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Orders</Data></Cell><Cell><Data ss:Type="Number">' . (int)($t['total_orders'] ?? 0) . '</Data></Cell></Row>';
            }
            if (isset($data['summary']['subscriptions'])) {
                $s = $data['summary']['subscriptions'];
                $xml .= '<Row/>';
                $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Subscriptions</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($s['total_revenue'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($s['net_revenue'] ?? 0) . '</Data></Cell></Row>';
            }
            if (isset($data['summary']['additional_tickets'])) {
                $a = $data['summary']['additional_tickets'];
                $xml .= '<Row/>';
                $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Additional Tickets</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($a['total_revenue'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($a['net_revenue'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Orders</Data></Cell><Cell><Data ss:Type="Number">' . (int)($a['total_orders'] ?? 0) . '</Data></Cell></Row>';
                $xml .= '<Row><Cell><Data ss:Type="String">Tickets</Data></Cell><Cell><Data ss:Type="Number">' . (int)($a['total_tickets'] ?? 0) . '</Data></Cell></Row>';
            }
            $xml .= '</Table></Worksheet>';

            $rt = strtolower((string)$reportType);
            $includeTickets = in_array($rt, ['all','events','tickets']);
            $includeSubs = in_array($rt, ['all','subscriptions']);
            $includeAddl = in_array($rt, ['all','additional_tickets']);
            $includePayouts = in_array($rt, ['all','payouts']);

            // Events sheet (conditional include) - Shows total sales and net value per event
            if ($includeTickets && !empty($data['details']['events'])) {
            $xml .= '<Worksheet ss:Name="Events"><Table>';
            $xml .= '<Row>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Event ID</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Event Title</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Event Date</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Event Status</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Organizer</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Total Sales (Gross)</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Net Value</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Transaction Fees</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">VAT Amount</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Total Fees</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Total Orders</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Paid Orders</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Average Order Value</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">First Sale Date</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Last Sale Date</Data></Cell>'
                . '</Row>';
            foreach ($data['details']['events'] as $event) {
                $xml .= '<Row>'
                    . '<Cell><Data ss:Type="Number">' . (int)($event['event_id'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['event_title'] ?? 'Unknown Event') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['event_date'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['event_status'] ?? 'Unknown') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['organizer_name'] ?? 'Unknown Organizer') . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['total_revenue'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['net_revenue'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['transaction_fees'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['vat_amount'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['total_fees'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="Number">' . (int)($event['total_orders'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="Number">' . (int)($event['paid_orders'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($event['average_order_value'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['first_sale_date'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($event['last_sale_date'] ?? '') . '</Data></Cell>'
                    . '</Row>';
            }
            $xml .= '</Table></Worksheet>';
            } else if ($includeTickets) {
            // Add empty sheet if events are included but no data
            $xml .= '<Worksheet ss:Name="Events"><Table>';
            $xml .= '<Row><Cell><Data ss:Type="String">No events found for the selected period</Data></Cell></Row>';
            $xml .= '</Table></Worksheet>';
            }

            // Subscription Sales sheet (conditional include)
            if ($includeSubs) {
            $xml .= '<Worksheet ss:Name="Subscription Sales"><Table>';
            $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Subscription ID</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Customer</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Email</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Plan</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Amount</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Status</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Payment Status</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Start</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">End</Data></Cell></Row>';
            if (!empty($data['details']['subscriptions'])) {
                foreach ($data['details']['subscriptions'] as $s) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="Number">' . (int)($s['subscription_id'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['customer_name'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['customer_email'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['plan_name'] ?? '') . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($s['amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['status'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['payment_status'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['start_date'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['end_date'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                }
            } else {
                $xml .= '<Row><Cell><Data ss:Type="String">No subscription sales found</Data></Cell></Row>';
            }
            $xml .= '</Table></Worksheet>';
            }

            // Additional Tickets Details sheet (conditional include)
            if ($includeAddl) {
            $xml .= '<Worksheet ss:Name="Additional Tickets Details"><Table>';
            $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Purchase ID</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Customer</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Email</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Plan</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Qty</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Paid At</Data></Cell></Row>';
            if (!empty($data['details']['additional_tickets'])) {
                foreach ($data['details']['additional_tickets'] as $p) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="Number">' . (int)($p['purchase_id'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['customer_name'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['customer_email'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['plan_name'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="Number">' . (int)($p['quantity'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($p['gross_amount'] ?? $p['total_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($p['net_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['paid_at'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                }
            } else {
                $xml .= '<Row><Cell><Data ss:Type="String">No additional ticket purchases found</Data></Cell></Row>';
            }
            $xml .= '</Table></Worksheet>';
            }

            // Payouts sheet (conditional include)
            if ($includePayouts) {
            $xml .= '<Worksheet ss:Name="Payouts"><Table>';
            $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Payout ID</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Event</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Organizer</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Status</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Processed At</Data></Cell></Row>';
            if (!empty($data['details']['payouts'])) {
                foreach ($data['details']['payouts'] as $row) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="Number">' . (int)($row['payout_id'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['event_title'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['organizer'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['status'] ?? '') . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['gross_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['net_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['processed_at'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                }
            } else {
                $xml .= '<Row><Cell><Data ss:Type="String">No payouts found</Data></Cell></Row>';
            }
            $xml .= '</Table></Worksheet>';
            }

            // Ticket Sales sheet (conditional include) - Based on ticket purchase table
            if ($includeTickets && !empty($data['details']['ticket_sales'])) {
            $xml .= '<Worksheet ss:Name="Ticket Sales"><Table>';
            $xml .= '<Row>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Purchase ID</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Buyer</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Email</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Event</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Ticket Type</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Status</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Quantity</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Total Amount (Gross)</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Transaction Fee</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">VAT Amount</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Net Amount</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Payment Method</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Created At</Data></Cell>'
                . '<Cell ss:StyleID="H"><Data ss:Type="String">Paid At</Data></Cell>'
                . '</Row>';
            foreach ($data['details']['ticket_sales'] as $row) {
                $xml .= '<Row>'
                    . '<Cell><Data ss:Type="Number">' . (int)($row['purchase_id'] ?? $row['id'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['buyer'] ?? 'Unknown') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['buyer_email'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['event_title'] ?? 'Unknown Event') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['ticket_type'] ?? 'Ticket') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['status'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="Number">' . (int)($row['quantity'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['total_amount'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['transaction_fee'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['vat_amount'] ?? 0) . '</Data></Cell>'
                    . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['net_amount'] ?? 0) . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['payment_method'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['created_at'] ?? '') . '</Data></Cell>'
                    . '<Cell><Data ss:Type="String">' . self::esc($row['paid_at'] ?? '') . '</Data></Cell>'
                    . '</Row>';
            }
            $xml .= '</Table></Worksheet>';
            } else if ($includeTickets) {
            // Add empty sheet if tickets are included but no data
            $xml .= '<Worksheet ss:Name="Ticket Sales"><Table>';
            $xml .= '<Row><Cell><Data ss:Type="String">No ticket sales found for the selected period</Data></Cell></Row>';
            $xml .= '</Table></Worksheet>';
            }

            // Invoice Summary (conditional rows based on report type)
            $xml .= '<Worksheet ss:Name="Invoice Summary"><Table>';
            $xml .= '<Row><Cell ss:StyleID="H"><Data ss:Type="String">Type</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Customer</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Email</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Item</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Qty</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Gross</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Net</Data></Cell><Cell ss:StyleID="H"><Data ss:Type="String">Paid At</Data></Cell></Row>';
            $invoiceRows = 0;
            if ($includeTickets && !empty($data['details']['ticket_sales'])) {
                foreach ($data['details']['ticket_sales'] as $row) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="String">Ticket</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['buyer'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['buyer_email'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc(($row['event_title'] ?? '') . ' - ' . ($row['ticket_type'] ?? 'Ticket')) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="Number">' . (int)($row['quantity'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['total_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($row['net_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($row['paid_at'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                    $invoiceRows++;
                }
            }
            if ($includeSubs && !empty($data['details']['subscriptions'])) {
                foreach ($data['details']['subscriptions'] as $s) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="String">Subscription</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['customer_name'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['customer_email'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc(($s['plan_name'] ?? '') . ' Plan') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="Number">1</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($s['amount'] ?? $s['gross_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($s['net_amount'] ?? $s['amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($s['start_date'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                    $invoiceRows++;
                }
            }
            if ($includeAddl && !empty($data['details']['additional_tickets'])) {
                foreach ($data['details']['additional_tickets'] as $p) {
                    $xml .= '<Row>'
                        . '<Cell><Data ss:Type="String">Additional Tickets</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['customer_name'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['customer_email'] ?? '') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc(($p['plan_name'] ?? '') . ' Addl Tix') . '</Data></Cell>'
                        . '<Cell><Data ss:Type="Number">' . (int)($p['quantity'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($p['gross_amount'] ?? $p['total_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell ss:StyleID="C"><Data ss:Type="Number">' . (float)($p['net_amount'] ?? 0) . '</Data></Cell>'
                        . '<Cell><Data ss:Type="String">' . self::esc($p['paid_at'] ?? '') . '</Data></Cell>'
                        . '</Row>';
                    $invoiceRows++;
                }
            }
            if ($invoiceRows === 0) {
                $xml .= '<Row><Cell><Data ss:Type="String">No invoices found</Data></Cell></Row>';
            }
            $xml .= '</Table></Worksheet>';

            $xml .= '</Workbook>';
            return $xml;
        }
        // NOTE: legacy builder below is retained but unreachable due to early return above
        
        $dateRange = $data['dateRange'] ?? 'All Time';
        $generatedAt = now()->format('Y-m-d H:i:s');
        
        $xml = '<?' . 'xml version="1.0" encoding="UTF-8"?>' . "\n";
        // Help Excel recognize XML Spreadsheet format explicitly
        $xml .= '<?' . 'mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Styles
        $xml .= '<Styles>' . "\n";
        
        // Header style - Teal background with white text
        $xml .= '<Style ss:ID="Header">' . "\n";
        $xml .= '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/>' . "\n";
        $xml .= '<Interior ss:Color="#14b8a6" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Summary style - Light teal background
        $xml .= '<Style ss:ID="Summary">' . "\n";
        $xml .= '<Font ss:Bold="1" ss:Color="#0f766e" ss:Size="11"/>' . "\n";
        $xml .= '<Interior ss:Color="#f0fdfa" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#14b8a6"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#14b8a6"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#14b8a6"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#14b8a6"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Alignment ss:Horizontal="Left" ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Currency style - Green text for positive amounts
        $xml .= '<Style ss:ID="Currency">' . "\n";
        // Avoid currency symbol in NumberFormat to prevent corruption in some Excel versions
        $xml .= '<NumberFormat ss:Format="#,##0.00"/>' . "\n";
        $xml .= '<Font ss:Color="#059669" ss:Bold="1"/>' . "\n";
        $xml .= '<Alignment ss:Horizontal="Right" ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Data row style - Alternating colors
        $xml .= '<Style ss:ID="DataRow">' . "\n";
        $xml .= '<Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Alternating row style
        $xml .= '<Style ss:ID="AltRow">' . "\n";
        $xml .= '<Interior ss:Color="#ffffff" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Borders>' . "\n";
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>' . "\n";
        $xml .= '</Borders>' . "\n";
        $xml .= '<Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Title style - Large bold text
        $xml .= '<Style ss:ID="Title">' . "\n";
        $xml .= '<Font ss:Bold="1" ss:Size="14" ss:Color="#0f766e"/>' . "\n";
        $xml .= '<Interior ss:Color="#ecfdf5" ss:Pattern="Solid"/>' . "\n";
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Info style - Light gray for metadata
        $xml .= '<Style ss:ID="Info">' . "\n";
        $xml .= '<Font ss:Color="#6b7280" ss:Size="10"/>' . "\n";
        $xml .= '<Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '</Style>' . "\n";
        
        $xml .= '</Styles>' . "\n";
        
        // Summary Sheet
        $xml .= '<Worksheet ss:Name="Summary">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Title
        $xml .= '<Row><Cell ss:StyleID="Title"><Data ss:Type="String">Admin Sales Report</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell ss:StyleID="Info"><Data ss:Type="String">Date Range: ' . self::esc($dateRange) . '</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell ss:StyleID="Info"><Data ss:Type="String">Generated: ' . self::esc($generatedAt) . '</Data></Cell></Row>' . "\n";
        $xml .= '<Row></Row>' . "\n";
        
        // Summary data
        if (isset($data['summary']['tickets'])) {
            $ticketSummary = $data['summary']['tickets'];
            $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">EVENT SALES SUMMARY</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Revenue (Gross)</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ticketSummary['total_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Net Revenue</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ticketSummary['net_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Transaction Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ticketSummary['transaction_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">VAT Amount</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ticketSummary['vat_amount'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ticketSummary['total_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Total Orders</Data></Cell><Cell ss:StyleID="AltRow"><Data ss:Type="Number">' . ($ticketSummary['total_orders'] ?? 0) . '</Data></Cell></Row>' . "\n";
            // Removed Pending Orders to avoid user confusion and keep sheet simpler
            $xml .= '<Row></Row>' . "\n";
        }
        
        if (isset($data['summary']['subscriptions'])) {
            $subSummary = $data['summary']['subscriptions'];
            $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">SUBSCRIPTION SALES SUMMARY</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Revenue (Gross)</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subSummary['total_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Net Revenue</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subSummary['net_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Transaction Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subSummary['transaction_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">VAT Amount</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subSummary['vat_amount'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subSummary['total_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Total Subscriptions</Data></Cell><Cell ss:StyleID="AltRow"><Data ss:Type="Number">' . ($subSummary['total_subscriptions'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Active Subscriptions</Data></Cell><Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . ($subSummary['active_subscriptions'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row></Row>' . "\n";
        }
        
        if (isset($data['summary']['additional_tickets'])) {
            $additionalTicketsSummary = $data['summary']['additional_tickets'];
            $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">ADDITIONAL TICKETS SUMMARY</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Revenue (Gross)</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['total_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Net Revenue</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['net_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Transaction Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['transaction_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">VAT Amount</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['vat_amount'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Fees</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['total_fees'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Total Orders</Data></Cell><Cell ss:StyleID="AltRow"><Data ss:Type="Number">' . ($additionalTicketsSummary['total_orders'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Total Tickets</Data></Cell><Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . ($additionalTicketsSummary['total_tickets'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Admin Revenue</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($additionalTicketsSummary['admin_revenue'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row></Row>' . "\n";
        }

        if (isset($data['summary']['payouts'])) {
            $pSummary = $data['summary']['payouts'];
            $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">PAYOUTS SUMMARY</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Processed Total (Net)</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($pSummary['processed_total'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Paid Total (Net)</Data></Cell><Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($pSummary['paid_total'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="DataRow"><Data ss:Type="String">Requests</Data></Cell><Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . ($pSummary['requests'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row><Cell ss:StyleID="AltRow"><Data ss:Type="String">Paid Count</Data></Cell><Cell ss:StyleID="AltRow"><Data ss:Type="Number">' . ($pSummary['paid_count'] ?? 0) . '</Data></Cell></Row>' . "\n";
            $xml .= '<Row></Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        
        // Event Earnings Sheet - Event Revenue Summary (only for 'all' or 'events')
        if ($reportType === 'all' || $reportType === 'events') {
        $xml .= '<Worksheet ss:Name="Event Earnings">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Headers for Event Revenue
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event ID</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event Title</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event Date</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Organizer</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Total Revenue</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Total Orders</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Paid Orders</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Pending Orders</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Average Order Value</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">First Sale Date</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Last Sale Date</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Data rows - Event Revenue Summary
        if (isset($data['details']['events']) && !empty($data['details']['events'])) {
            $rowIndex = 0;
            foreach ($data['details']['events'] as $event) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($event['event_id'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['event_title'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['event_date'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['event_status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['organizer_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($event['total_revenue'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($event['total_orders'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($event['paid_orders'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($event['pending_orders'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($event['average_order_value'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['first_sale_date'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($event['last_sale_date'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
            }
        } else {
            // No data message
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:Index="2" ss:StyleID="Info"><Data ss:Type="String">No event sales data found for the selected period</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        // Totals row for Event Sales with admin percentage
        if (isset($data['details']['events']) && !empty($data['details']['events'])) {
            $totalRevenue = 0; $totalOrders = 0; $totalPaid = 0; $totalPending = 0; $aov = 0; $count=0;
            foreach ($data['details']['events'] as $event) {
                $totalRevenue += ($event['total_revenue'] ?? 0);
                $totalOrders += ($event['total_orders'] ?? 0);
                $totalPaid += ($event['paid_orders'] ?? 0);
                $totalPending += ($event['pending_orders'] ?? 0);
                $aov += ($event['average_order_value'] ?? 0);
                $count++;
            }
            $adminPct = (float)($data['admin_revenue_percentage'] ?? 0);
            $adminTotal = $totalRevenue * ($adminPct / 100);
            $xml .= '<Row></Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
            $xml .= '<Cell/><Cell/><Cell/><Cell/>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $totalRevenue . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $totalOrders . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $totalPaid . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $totalPending . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($count ? ($aov/$count) : 0) . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">ADMIN ' . $adminPct . '%</Data></Cell>' . "\n";
            $xml .= '<Cell ss:MergeAcross="9" ss:StyleID="Currency"><Data ss:Type="Number">' . $adminTotal . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Worksheet>' . "\n";
        }
        
        // Subscription Sales Sheet (only for 'all' or 'subscriptions')
        if ($reportType === 'all' || $reportType === 'subscriptions') {
        $xml .= '<Worksheet ss:Name="Subscription Sales">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Headers
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Subscription ID</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Customer Name</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Customer Email</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Plan Name</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Plan Duration</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Amount</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Payment Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Start Date</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">End Date</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Created At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Data rows
        if (isset($data['details']['subscriptions']) && !empty($data['details']['subscriptions'])) {
            $rowIndex = 0;
            foreach ($data['details']['subscriptions'] as $subscription) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($subscription['subscription_id'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['customer_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['customer_email'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['plan_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['plan_duration'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($subscription['amount'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['payment_status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['start_date'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['end_date'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($subscription['created_at'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
            }
        } else {
            // No data message
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:Index="2" ss:StyleID="Info"><Data ss:Type="String">No subscription sales data found for the selected period</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        }

        // Additional Tickets Details Sheet (only for 'all' or 'additional_tickets')
        if ($reportType === 'all' || $reportType === 'additional_tickets') {
            $xml .= '<Worksheet ss:Name="Additional Tickets Details">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Headers
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Purchase ID</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Customer Name</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Customer Email</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Plan Name</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Plan Price</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Quantity</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Total Amount</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Payment Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Created At</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Paid At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Data rows
        if (isset($data['details']['additional_tickets']) && !empty($data['details']['additional_tickets'])) {
            $rowIndex = 0;
            foreach ($data['details']['additional_tickets'] as $purchase) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($purchase['purchase_id'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['customer_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['customer_email'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['plan_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($purchase['plan_price'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($purchase['quantity'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($purchase['total_amount'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['payment_status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['created_at'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($purchase['paid_at'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
            }
        } else {
            // No data message
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:Index="2" ss:StyleID="Info"><Data ss:Type="String">No additional ticket purchases found for the selected period</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        }

        // Additional Tickets Sheet (only for 'all' or 'additional_tickets')
        if ($reportType === 'all' || $reportType === 'additional_tickets') {
        $xml .= '<Worksheet ss:Name="Additional Tickets">' . "\n";
        $xml .= '<Table>' . "\n";

        // Summary by Promo (Pricing Plan)
        if (isset($data['ticketLimitPlans']) && !empty($data['ticketLimitPlans'])) {
            $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">SUMMARY BY PROMO</Data></Cell></Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Promo</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Price</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Orders</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Tickets</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Revenue</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";

            $rowIndex = 0; $sumRevenue=0; $sumOrders=0; $sumTickets=0;
            foreach ($data['ticketLimitPlans'] as $plan) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($plan['plan_name'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($plan['plan_price'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($plan['total_purchases'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($plan['total_tickets'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($plan['total_revenue'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
                $sumRevenue += ($plan['total_revenue'] ?? 0);
                $sumOrders += ($plan['total_purchases'] ?? 0);
                $sumTickets += ($plan['total_tickets'] ?? 0);
            }

            $xml .= '<Row></Row>' . "\n";
            $adminPct = (float)($data['admin_revenue_percentage'] ?? 0);
            $adminTotal = $sumRevenue * ($adminPct / 100);
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
            $xml .= '<Cell/><Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $sumOrders . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $sumTickets . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $sumRevenue . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">ADMIN ' . $adminPct . '%</Data></Cell>' . "\n";
            $xml .= '<Cell ss:MergeAcross="3" ss:StyleID="Currency"><Data ss:Type="Number">' . $adminTotal . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        }

        // Details by Event (kept within Additional Tickets worksheet)
        $xml .= '<Row><Cell ss:StyleID="Summary"><Data ss:Type="String">ADDITIONAL TICKETS BY EVENT</Data></Cell></Row>' . "\n";
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event ID</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event Title</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Orders</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Tickets</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Revenue</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";

        if (isset($data['ticketLimitByEvent']) && !empty($data['ticketLimitByEvent'])) {
            $rowIndex = 0; $evRevenue=0; $evOrders=0; $evTickets=0;
            foreach ($data['ticketLimitByEvent'] as $row) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($row['event_id'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($row['title'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($row['orders'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($row['tickets'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['revenue'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
                $evRevenue += ($row['revenue'] ?? 0);
                $evOrders += ($row['orders'] ?? 0);
                $evTickets += ($row['tickets'] ?? 0);
            }
            $adminPct = (float)($data['admin_revenue_percentage'] ?? 0);
            $adminTotal = $evRevenue * ($adminPct / 100);
            $xml .= '<Row></Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
            $xml .= '<Cell/><Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $evOrders . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="DataRow"><Data ss:Type="Number">' . $evTickets . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $evRevenue . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">ADMIN ' . $adminPct . '%</Data></Cell>' . "\n";
            $xml .= '<Cell ss:MergeAcross="3" ss:StyleID="Currency"><Data ss:Type="Number">' . $adminTotal . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        } else {
            $xml .= '<Row><Cell ss:Index="2" ss:StyleID="Info"><Data ss:Type="String">No additional ticket data found for the selected period</Data></Cell></Row>' . "\n";
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        }
        
        // Payouts Details Sheet (only for 'all' or 'payouts')
        if ($reportType === 'all' || $reportType === 'payouts') {
        $xml .= '<Worksheet ss:Name="Payouts">' . "\n";
        $xml .= '<Table>' . "\n";
        // Headers
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Payout ID</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Organizer</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Gross Amount</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Fee</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Net Amount</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Requested At</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Processed At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";

        if (isset($data['details']['payouts']) && !empty($data['details']['payouts'])) {
            $rowIndex = 0; $sumGross=0; $sumFee=0; $sumNet=0;
            foreach ($data['details']['payouts'] as $row) {
                $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
                $xml .= '<Row>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($row['payout_id'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($row['event_title'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($row['organizer'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . ($row['status'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['gross_amount'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['fee_amount'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['net_amount'] ?? 0) . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . ($row['requested_at'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . ($row['processed_at'] ?? '') . '</Data></Cell>' . "\n";
                $xml .= '</Row>' . "\n";
                $rowIndex++;
                $sumGross += ($row['gross_amount'] ?? 0);
                $sumFee += ($row['fee_amount'] ?? 0);
                $sumNet += ($row['net_amount'] ?? 0);
            }
            $xml .= '<Row></Row>' . "\n";
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="Summary"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
            $xml .= '<Cell/><Cell/><Cell/>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $sumGross . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $sumFee . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $sumNet . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
        } else {
            $xml .= '<Row><Cell ss:Index="2" ss:StyleID="Info"><Data ss:Type="String">No payout data found for the selected period</Data></Cell></Row>' . "\n";
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";

        // Ticket Purchases (raw) worksheet
        if (($reportType === 'all' || $reportType === 'events') && isset($data['details']['ticket_sales'])) {
        $xml .= '<Worksheet ss:Name="Ticket Purchases">' . "\n";
        $xml .= '<Table>' . "\n";
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Buyer</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Email</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Ticket Type</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Qty</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Gross</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Transaction Fee</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">VAT</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Net</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Payment Method</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Created At</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Paid At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";

        $rowIndex = 0;
        foreach ($data['details']['ticket_sales'] as $row) {
            $style = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['buyer'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['buyer_email'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['event_title'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['ticket_type'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['status'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . ($row['quantity'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['total_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['transaction_fee'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['vat_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($row['net_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['payment_method'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['created_at'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . self::esc($row['paid_at'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $rowIndex++;
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        }

        // Ticket Sales worksheet (only for 'all' or 'events')
        if (($reportType === 'all' || $reportType === 'events') && isset($data['details']['ticket_sales'])) {
        $xml .= '<Worksheet ss:Name="Ticket Sales">' . "\n";
        $xml .= '<Table>' . "\n";
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Buyer</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Email</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Event</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Ticket Type</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Qty</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Gross</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Fee</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">VAT</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Net</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Paid At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";

        $rowIndex = 0;
        foreach ($data['details']['ticket_sales'] as $ts) {
            $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['buyer']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['buyer_email']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['event_title']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['ticket_type']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['status']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($ts['quantity'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ts['total_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ts['transaction_fee'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ts['vat_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($ts['net_amount'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($ts['paid_at'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $rowIndex++;
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        }

        // Invoice Summary Sheet (common fields across tickets, subs, addl)
        $xml .= '<Worksheet ss:Name="Invoice Summary">' . "\n";
        $xml .= '<Table>' . "\n";
        $xml .= '<Row>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Type</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Customer</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Email</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Item</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Qty</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Gross</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Transaction Fee</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Net</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Payment Method</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Created At</Data></Cell>' . "\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Paid At</Data></Cell>' . "\n";
        $xml .= '</Row>' . "\n";
        
        // Combine ticket and subscription data for invoice summary
        $invoiceData = [];
        
        // Tickets (use ticket_sales rows)
        if (($reportType === 'all' || $reportType === 'events') && isset($data['details']['ticket_sales'])) {
            foreach ($data['details']['ticket_sales'] as $row) {
                $invoiceData[] = [
                    'type' => 'Ticket',
                    'customer_name' => $row['buyer'] ?? '',
                    'customer_email' => $row['buyer_email'] ?? '',
                    'item' => ($row['event_title'] ?? '') . ' - ' . ($row['ticket_type'] ?? ''),
                    'quantity' => $row['quantity'] ?? 0,
                    'gross' => $row['total_amount'] ?? 0,
                    'transaction_fee' => $row['transaction_fee'] ?? 0,
                    'net' => $row['net_amount'] ?? 0,
                    'payment_method' => $row['payment_method'] ?? '',
                    'created_at' => $row['created_at'] ?? '',
                    'paid_at' => $row['paid_at'] ?? ''
                ];
            }
        }
        
        // Add subscriptions as invoices (only for 'all' or 'subscriptions')
        if (($reportType === 'all' || $reportType === 'subscriptions') && isset($data['details']['subscriptions'])) {
            foreach ($data['details']['subscriptions'] as $subscription) {
                $invoiceData[] = [
                    'type' => 'Subscription',
                    'customer_name' => $subscription['customer_name'] ?? '',
                    'customer_email' => $subscription['customer_email'] ?? '',
                    'item' => ($subscription['plan_name'] ?? '') . ' Plan',
                    'quantity' => 1,
                    'gross' => $subscription['gross_amount'] ?? ($subscription['amount'] ?? 0),
                    'transaction_fee' => $subscription['transaction_fee'] ?? 0,
                    'net' => $subscription['net_amount'] ?? ($subscription['amount'] ?? 0),
                    'payment_method' => $subscription['payment_method'] ?? '',
                    'created_at' => $subscription['created_at'] ?? '',
                    'paid_at' => $subscription['start_date'] ?? ''
                ];
            }
        }
        
        // Add additional tickets as invoices (only for 'all' or 'additional_tickets')
        if (($reportType === 'all' || $reportType === 'additional_tickets') && isset($data['details']['additional_tickets'])) {
            foreach ($data['details']['additional_tickets'] as $purchase) {
                $invoiceData[] = [
                    'type' => 'Additional Tickets',
                    'customer_name' => $purchase['customer_name'] ?? '',
                    'customer_email' => $purchase['customer_email'] ?? '',
                    'item' => ($purchase['plan_name'] ?? '') . ' Addl Tix',
                    'quantity' => $purchase['quantity'] ?? 0,
                    'gross' => $purchase['gross_amount'] ?? 0,
                    'transaction_fee' => $purchase['transaction_fee'] ?? 0,
                    'net' => $purchase['net_amount'] ?? (($purchase['gross_amount'] ?? 0) - ($purchase['transaction_fee'] ?? 0) - ($purchase['vat_amount'] ?? 0)),
                    'payment_method' => $purchase['payment_method'] ?? '',
                    'created_at' => $purchase['created_at'] ?? '',
                    'paid_at' => $purchase['paid_at'] ?? ''
                ];
            }
        }
        
        // Data rows
        $rowIndex = 0;
        foreach ($invoiceData as $invoice) {
            $rowStyle = ($rowIndex % 2 == 0) ? 'DataRow' : 'AltRow';
            $xml .= '<Row>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['type']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['customer_name']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['customer_email']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['item']) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . ($invoice['quantity'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($invoice['gross'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($invoice['transaction_fee'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . ($invoice['net'] ?? 0) . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['payment_method'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['created_at'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . self::esc($invoice['paid_at'] ?? '') . '</Data></Cell>' . "\n";
            $xml .= '</Row>' . "\n";
            $rowIndex++;
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        
        $xml .= '</Workbook>';
        
        return $xml;
    }
}
}
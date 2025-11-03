<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserExport
{
    /**
     * Export all users to Excel format with colors
     */
    public function export(string $format = 'csv', string $type = 'all')
    {
        $query = User::with(['subscriptions.plan'])
            ->withCount(['events', 'ticketPurchases'])
            ->whereNotIn('email', function($q) {
                $q->select('email')
                  ->from('admins')
                  ->where('role', 'admin');
            }); // Exclude admin users
        
        // Apply filters based on type
        if ($type === 'verified') {
            $query->where('organizer', true)->whereNotNull('verified_organizer');
        } elseif ($type === 'pending') {
            $query->whereHas('organizerVerifications', function($q) {
                $q->where('status', 'pending');
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->get();

        // Generate Excel file
        return $this->generateExcel($users, $type);
    }

    private function generateExcel($users, $type = 'all')
    {
        $filename = 'users_export_' . date('Y-m-d_His') . '.xls';
        
        // Create Excel XML format
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        
        // Styles
        $xml .= '<Styles>';
        
        // Header style - Teal background
        $xml .= '<Style ss:ID="Header">';
        $xml .= '<Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/>';
        $xml .= '<Interior ss:Color="#14b8a6" ss:Pattern="Solid"/>';
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
        $xml .= '<Borders>';
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>';
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>';
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>';
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>';
        $xml .= '</Borders>';
        $xml .= '</Style>';
        
        // Title style
        $xml .= '<Style ss:ID="Title">';
        $xml .= '<Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/>';
        $xml .= '<Interior ss:Color="#0f766e" ss:Pattern="Solid"/>';
        $xml .= '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
        $xml .= '</Style>';
        
        // Even row style - Light gray
        $xml .= '<Style ss:ID="EvenRow">';
        $xml .= '<Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>';
        $xml .= '<Borders>';
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '</Borders>';
        $xml .= '</Style>';
        
        // Odd row style - White
        $xml .= '<Style ss:ID="OddRow">';
        $xml .= '<Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>';
        $xml .= '<Borders>';
        $xml .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>';
        $xml .= '</Borders>';
        $xml .= '</Style>';
        
        // Verified Organizer style - Green highlight
        $xml .= '<Style ss:ID="Verified">';
        $xml .= '<Font ss:Bold="1" ss:Color="#065f46"/>';
        $xml .= '<Interior ss:Color="#d1fae5" ss:Pattern="Solid"/>';
        $xml .= '</Style>';
        
        // Active Subscription style - Blue
        $xml .= '<Style ss:ID="ActiveSub">';
        $xml .= '<Font ss:Bold="1" ss:Color="#1e40af"/>';
        $xml .= '<Interior ss:Color="#dbeafe" ss:Pattern="Solid"/>';
        $xml .= '</Style>';
        
        $xml .= '</Styles>';
        
        // Worksheet
        $xml .= '<Worksheet ss:Name="Users Export">';
        $xml .= '<Table>';
        
        // Title row
        $titleText = 'User Report';
        if ($type === 'verified') {
            $titleText = 'Verified Organizers Report';
        } elseif ($type === 'pending') {
            $titleText = 'Pending Verifications Report';
        }
        
        $xml .= '<Row ss:Height="35">';
        $xml .= '<Cell ss:MergeAcross="14" ss:StyleID="Title"><Data ss:Type="String">' . htmlspecialchars($titleText) . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Date row
        $xml .= '<Row>';
        $xml .= '<Cell ss:MergeAcross="14"><Data ss:Type="String">Exported on: ' . now()->format('Y-m-d H:i:s') . ' | Total Users: ' . count($users) . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Header row
        $xml .= '<Row ss:Height="25">';
        $headers = [
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 
            'Email Verified', 'Organizer Status', 'Verified Organizer',
            'Subscription Plan', 'Subscription Status', 'Subscription End Date',
            'Total Events', 'Total Tickets', 'Last Login', 'Created At'
        ];
        
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        
        // Data rows
        $rowNumber = 0;
        foreach ($users as $user) {
            $rowNumber++;
            $isEven = ($rowNumber % 2 == 0);
            $rowStyle = $isEven ? 'EvenRow' : 'OddRow';
            
            $xml .= '<Row ss:Height="20">';
            
            // Get subscription info
            $subscription = $user->subscriptions->where('status', 'active')->first() ?? $user->subscriptions->sortByDesc('created_at')->first();
            $planName = $subscription ? ($subscription->plan->name ?? 'N/A') : 'No Subscription';
            $planStatus = $subscription ? ucfirst($subscription->status ?? 'N/A') : 'N/A';
            $planEndDate = $subscription && $subscription->end_date ? $subscription->end_date->format('Y-m-d') : 'N/A';
            
            // Get organizer status
            $isOrganizer = $user->organizer ? 'Yes' : 'No';
            $verifiedOrganizer = $user->verified_organizer ? 'Verified' : 'Not Verified';
            
            // Use eager loaded counts
            $eventsCount = $user->events_count ?? 0;
            $ticketsCount = $user->ticket_purchases_count ?? 0;
            $lastLoginFormatted = 'N/A';
            
            // Prepare data
            $rowData = [
                $user->id,
                $user->first_name ?? '',
                $user->last_name ?? '',
                $user->email ?? '',
                $user->phone ?? '',
                $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : 'Not Verified',
                $isOrganizer,
                $verifiedOrganizer,
                $planName,
                $planStatus,
                $planEndDate,
                $eventsCount,
                $ticketsCount,
                $lastLoginFormatted,
                $user->created_at->format('Y-m-d H:i:s')
            ];
            
            // Output cells
            foreach ($rowData as $index => $data) {
                $cellStyle = $rowStyle;
                
                // Color-code verified organizers (column 7)
                if ($index == 7 && $data === 'Verified') {
                    $cellStyle = 'Verified';
                }
                
                // Color-code active subscriptions (column 9)
                if ($index == 9 && $data === 'Active') {
                    $cellStyle = 'ActiveSub';
                }
                
                $xml .= '<Cell ss:StyleID="' . $cellStyle . '"><Data ss:Type="String">' . htmlspecialchars($data) . '</Data></Cell>';
            }
            
            $xml .= '</Row>';
        }
        
        $xml .= '</Table>';
        $xml .= '</Worksheet>';
        $xml .= '</Workbook>';
        
        // Save to temporary file
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFile, $xml);
        
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ])->deleteFileAfterSend(true);
    }

    /**
     * Get users count for export stats
     */
    public function getCount(): int
    {
        return User::whereNotIn('email', function($q) {
            $q->select('email')
              ->from('admins')
              ->where('role', 'admin');
        })->count();
    }
}

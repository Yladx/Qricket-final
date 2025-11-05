<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\EventStaff;
use App\Models\TicketLimitPurchase;
use App\Models\TicketLimitPricing;
use App\Models\SupportTicket;
use App\Models\SupportReply;
use App\Models\SocialMediaLink;
use App\Models\Admin;
use App\Models\TicketPurchase;
use App\Models\EventTicketInformation;
use App\Models\EventPayoutRequest;
use App\Models\EventVerificationRequest;
use App\Models\OrganizerVerification;

class DemoEventDataSeeder extends Seeder
{
    /**
     * Seed demonstration organizers, events, tickets, staff, social links,
     * support ticket, and sample sales for the ended event.
     */
    public function run(): void
    {
        // --- Create two organizers and one regular user ---
        $organizer1 = User::firstOrCreate(
            ['email' => 'organizer1@qricket.test'],
            [
                'first_name' => 'Alex',
                'last_name'  => 'Organizer',
                'password'   => Hash::make('password'),
                'organizer'  => true,
                'status'     => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $organizer2 = User::firstOrCreate(
            ['email' => 'organizer2@qricket.test'],
            [
                'first_name' => 'Blake',
                'last_name'  => 'Manager',
                'password'   => Hash::make('password'),
                'organizer'  => true,
                'status'     => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $attendee = User::firstOrCreate(
            ['email' => 'user@qricket.test'],
            [
                'first_name' => 'Taylor',
                'last_name'  => 'Guest',
                'password'   => Hash::make('password'),
                'organizer'  => false,
                'status'     => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // --- Social links for users ---
        $links = [
            $organizer1->id => [
                ['platform' => 'facebook', 'username' => 'alex.organizer'],
                ['platform' => 'linkedin', 'username' => 'alex-organizer'],
            ],
            $organizer2->id => [
                ['platform' => 'instagram', 'username' => 'blake.events'],
            ],
            $attendee->id => [
                ['platform' => 'twitter', 'username' => 'taylor_guest'],
            ],
        ];

        foreach ($links as $userId => $rows) {
            foreach ($rows as $row) {
                SocialMediaLink::firstOrCreate(
                    ['user_id' => $userId, 'platform' => $row['platform']],
                    [
                        'user_id'  => $userId,
                        'platform' => $row['platform'],
                        'username' => $row['username'],
                        'url'      => null,
                    ]
                );
            }
        }

        // --- Create three events: 1 past, 2 upcoming ---
        $now = now();

        $eventPast = Event::create([
            'user_id' => $organizer1->id,
            'title' => 'QRicket Summit 2023 (Past)',
            'description' => 'A past community meetup with talks and networking.',
            'category' => 'Conference',
            'is_free' => false,
            'start_date' => $now->copy()->subDays(10),
            'end_date' => $now->copy()->subDays(9),
            'location' => 'Metro Hall',
            'location_url' => 'https://maps.example.com/metro-hall',
            'status' => 'approved',
        ]);

        $eventUpcoming1 = Event::create([
            'user_id' => $organizer1->id,
            'title' => 'QRicket Live: Launch Night',
            'description' => 'Kickoff night with live demos and Q&A.',
            'category' => 'Launch',
            'is_free' => false,
            'start_date' => $now->copy()->addDays(5)->setTime(18,0),
            'end_date' => $now->copy()->addDays(5)->setTime(22,0),
            'location' => 'City Convention Center',
            'location_url' => 'https://maps.example.com/convention',
            'status' => 'approved',
        ]);

        $eventUpcoming2 = Event::create([
            'user_id' => $organizer2->id,
            'title' => 'Community Hackday',
            'description' => 'A hands-on build day for local developers.',
            'category' => 'Hackathon',
            'is_free' => true,
            'start_date' => $now->copy()->addDays(20)->setTime(9,0),
            'end_date' => $now->copy()->addDays(20)->setTime(17,0),
            'location' => 'Tech Park',
            'location_url' => 'https://maps.example.com/techpark',
            'status' => 'approved',
        ]);

        // --- Ticket types per event ---
        $events = collect([$eventPast, $eventUpcoming1, $eventUpcoming2]);
        $eventTickets = [];
        foreach ($events as $idx => $ev) {
            $basePrice = $ev->is_free ? 0 : 500;

            $ga = EventTicket::create([
                'event_id' => $ev->id,
                'name' => 'General Admission',
                'description' => 'Standard entry',
                'price' => $basePrice,
                'quantity' => 100,
                'sold_count' => $idx === 0 ? 2 : 0,
                'sale_start' => $ev->start_date->copy().'' , // safe default for preview
                'sale_end' => $ev->end_date,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $vip = EventTicket::create([
                'event_id' => $ev->id,
                'name' => 'VIP',
                'description' => 'Priority access & freebies',
                'price' => $ev->is_free ? 0 : 1000,
                'quantity' => 50,
                'sold_count' => 0,
                'sale_start' => $ev->start_date->copy(),
                'sale_end' => $ev->end_date,
                'is_active' => true,
                'sort_order' => 2,
            ]);

            $eventTickets[$ev->id] = [$ga, $vip];
        }

        // --- Cross-assign organizers as staff on each other's events ---
        foreach ([[$eventPast, $organizer1, $organizer2], [$eventUpcoming1, $organizer1, $organizer2], [$eventUpcoming2, $organizer2, $organizer1]] as $tuple) {
            [$e, $owner, $other] = $tuple;
            EventStaff::firstOrCreate(
                ['event' => $e->id, 'user_id' => $owner->id],
                [
                    'event_id' => $e->id,
                    'user_id' => $owner->id,
                    'role' => 'organizer',
                    'can_manage_event' => true,
                    'can_manage_staff' => true,
                    'can_manage_news' => true,
                    'can_manage_attendance' => true,
                    'can_manage_ticket' => true,
                    'can_manage_sales' => true,
                ]
            );

            EventStaff::firstOrCreate(
                ['event' => $e->id, 'user_id' => $other->id],
                [
                    'event_id' => $e->id,
                    'user_id' => $other->id,
                    'role' => 'manager',
                    'can_manage_event' => true,
                    'can_manage_staff' => true,
                    'can_manage_news' => true,
                    'can_manage_attendance' => true,
                    'can_manage_ticket' => true,
                    'can_manage_sales' => true,
                ]
            );
        }

        // --- Random additional tickets for one event ---
        $targetEvent = $events->random();
        $type = $targetEvent->is_free ? 'free' : 'paid';
        $pricing = TicketLimitPricing::where('type', $type)->inRandomOrder()->first();
        if ($pricing) {
            TicketLimitPurchase::create([
                'user_id' => $targetEvent->user_id,
                'event_id' => $targetEvent->id,
                'ticket_limit_pricing_id' => $pricing->id,
                'additional_tickets' => $pricing->ticket_quantity,
                'amount' => $pricing->price,
                'currency' => $pricing->currency,
                'status' => 'paid',
                'payment_method' => 'CREDIT_CARD',
                'payment_reference' => 'SIM-' . Str::upper(Str::random(8)),
                'purchase_id' => 'ADDTIX_' . time(),
                'paid_at' => now(),
            ]);
        }

        // --- Sales for the ended event: create a paid purchase & issue tickets ---
        $pastTypes = $eventTickets[$eventPast->id] ?? [];
        if (!empty($pastTypes)) {
            $gaType = $pastTypes[0];
            $qty = 2; // two tickets sold
            $unit = (float) $gaType->price; // 500 for paid event
            $total = round($unit * $qty, 2);
            $txFee = round($total * 0.025, 2); // sample gateway fee
            $vat = 0.00;
            $net = round($total - $txFee, 2);

            $purchase = TicketPurchase::create([
                'user_id' => $attendee->id,
                'event_id' => $eventPast->id,
                'ticket_type_id' => $gaType->id,
                'purchase_id' => TicketPurchase::generatePurchaseId(),
                'quantity' => $qty,
                'unit_price' => $unit,
                'total_amount' => $total,
                'transaction_fee' => $txFee,
                'vat_amount' => $vat,
                'net_amount' => $net,
                'total_fees' => $txFee + $vat,
                'currency' => 'PHP',
                'payment_method' => 'CREDIT_CARD',
                'status' => 'paid',
                'paid_at' => now()->subDays(9),
                'customer_details' => [
                    'name' => $attendee->full_name,
                    'email' => $attendee->email,
                ],
            ]);

            // Issue two ticket instances for the purchase
            for ($i = 0; $i < $qty; $i++) {
                $ti = EventTicketInformation::create([
                    'user_id' => $attendee->id,
                    'event_id' => $eventPast->id,
                    'ticket_type_id' => $gaType->id,
                    'ticket_purchase_id' => $purchase->id,
                    'certificate_given' => false,
                ]);
                $ti->load('ticketPurchase');
                try {
                    $ti->qr_code_data = $ti->generateQRCodeData();
                    $ti->save();
                } catch (\Throwable $e) {
                    // Ignore QR generation errors during seeding
                }
                // Mark the first ticket as checked-in and issued a certificate to simulate attendance
                if ($i === 0) {
                    $ti->checked_in_at = $eventPast->start_date->copy()->addHour();
                    $ti->certificate_given = true;
                    $ti->save();
                }
            }

            // Create a payout record for the ended event
            $qricketFee = round($total * 0.05, 2);
            EventPayoutRequest::firstOrCreate(
                [
                    'event_id' => $eventPast->id,
                    'organizer_id' => $organizer1->id,
                ],
                [
                    'total_earnings' => $total,
                    'qricket_fee' => $qricketFee,
                    'net_amount' => max(0, $total - $qricketFee),
                    'status' => 'paid',
                    'requested_at' => now()->subDays(8),
                    'processed_at' => now()->subDays(7),
                    'notes' => 'Auto‑generated demo payout'
                ]
            );
        }

        // --- Organizer verification requests (KYC) ---
        $admin = Admin::first();
        OrganizerVerification::firstOrCreate(
            ['user_id' => $organizer1->id],
            [
                'user_id' => $organizer1->id,
                'status' => 'approved',
                'organization_name' => 'Alex Events Co.',
                'document_type' => 'id_card',
                'document_front_path' => 'verifications/' . $organizer1->id . '/front.png',
                'document_back_path' => 'verifications/' . $organizer1->id . '/back.png',
                'contact_phone' => '+63 900 000 0001',
                'terms_accepted' => true,
                'terms_accepted_at' => now()->subDays(30),
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(29),
            ]
        );

        OrganizerVerification::firstOrCreate(
            ['user_id' => $organizer2->id],
            [
                'user_id' => $organizer2->id,
                'status' => 'pending',
                'organization_name' => 'Blake Productions',
                'document_type' => 'passport',
                'document_file_path' => 'verifications/' . $organizer2->id . '/passport.png',
                'contact_phone' => '+63 900 000 0002',
                'terms_accepted' => true,
                'terms_accepted_at' => now()->subDays(2),
            ]
        );

        // --- Event verification requests (moderation) ---
        EventVerificationRequest::firstOrCreate(
            ['event_id' => $eventPast->id],
            [
                'event_id' => $eventPast->id,
                'organizer_id' => $organizer1->id,
                'status' => 'approved',
                'notes' => 'Auto‑approved for demo data',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(9),
                'submitted_at' => now()->subDays(12),
            ]
        );

        EventVerificationRequest::firstOrCreate(
            ['event_id' => $eventUpcoming1->id],
            [
                'event_id' => $eventUpcoming1->id,
                'organizer_id' => $organizer1->id,
                'status' => 'approved',
                'notes' => 'Approved upcoming launch.',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDay(),
                'submitted_at' => now()->subDays(3),
            ]
        );

        EventVerificationRequest::firstOrCreate(
            ['event_id' => $eventUpcoming2->id],
            [
                'event_id' => $eventUpcoming2->id,
                'organizer_id' => $organizer2->id,
                'status' => 'pending',
                'notes' => 'Awaiting review',
                'submitted_at' => now()->subDay(),
            ]
        );

        // --- Seed a sample support ticket and admin reply ---
        $ticket = SupportTicket::create([
            'user_id' => $attendee->id,
            'name' => $attendee->full_name,
            'email' => $attendee->email,
            'subject' => 'Question about event tickets',
            'message' => 'Hi team, can I change the name on my ticket after purchase?',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        if ($admin) {
            SupportReply::create([
                'ticket_id' => $ticket->id,
                'admin_id' => $admin->id,
                'message' => 'Yes, you can update attendee details from your My Tickets page. Let us know if you need help.',
                'is_admin_reply' => true,
            ]);
        }
    }
}



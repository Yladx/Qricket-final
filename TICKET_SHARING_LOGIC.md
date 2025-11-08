# Ticket Sharing Logic Documentation

## Overview
The ticket sharing system allows users to share their event tickets with other users. The system uses a request/accept/decline flow with notifications and proper ownership transfer.

## Database Schema

### EventTicketShare Model
**Table:** `event_ticket_shares`

**Key Fields:**
- `id` - Share record ID
- `event_id` - The event the ticket belongs to
- `ticket_type_id` - The type of ticket being shared
- `sharer_id` - User who is sharing the ticket
- `recipient_id` - User who will receive the ticket
- `shared_ticket_id` - Specific ticket instance being shared
- `quantity` - Number of tickets (usually 1)
- `status` - `pending`, `accepted`, or `declined`
- `expires_at` - Share expiration date (7 days from creation)
- `message` - Optional message from sharer
- `created_at`, `updated_at` - Timestamps

### EventTicketInformation Model
**Table:** `event_ticket_information`

**Key Fields for Sharing:**
- `id` - Ticket instance ID
- `user_id` - Current owner of the ticket
- `shared_by_user_id` - Original sharer (for tracking)
- `shared_at` - When ticket was shared/accepted
- `event_id` - Event the ticket is for
- `ticket_type_id` - Type of ticket

## API Endpoints

### 1. Share Tickets
**Route:** `POST /api/tickets/share`
**Controller:** `App\Http\Controllers\Api\EventTicketShareController@share`

**Request:**
```json
{
  "event_id": 1,
  "users": [2, 3, 4],
  "message": "Optional message"
}
```

**Logic Flow:**
1. **Validation:**
   - Validates event_id exists
   - Validates users array (min 1 user)
   - Validates each user exists
   - Validates message (optional, max 500 chars)

2. **Get Available Tickets:**
   - Finds tickets owned by sharer for the event
   - Excludes tickets that already have pending shares
   - Checks if sharer has enough tickets for all recipients

3. **Create Share Records:**
   - For each recipient:
     - Skips if recipient is the sharer
     - Gets next available ticket
     - Checks if ticket already has pending share
     - Creates or updates share record:
       - Status: `pending`
       - Expires: 7 days from now
       - Links specific ticket via `shared_ticket_id`
     - Creates notification for recipient
     - Logs activity

4. **Response:**
   ```json
   {
     "success": true,
     "message": "Successfully shared X ticket(s) with Y user(s).",
     "shares_created": 3
   }
   ```

### 2. Accept Ticket Share
**Route:** `POST /api/tickets/share/{id}/accept`
**Controller:** `App\Http\Controllers\Api\EventTicketShareController@accept`

**Logic Flow:**
1. **Validation:**
   - Finds share record by ID
   - Verifies recipient is authenticated user
   - Checks status is `pending`
   - Verifies share hasn't expired

2. **Transfer Ticket:**
   - Gets the specific ticket to transfer
   - Updates ticket ownership:
     - `user_id` → recipient_id
     - `shared_by_user_id` → sharer_id (tracking)
     - `shared_at` → now()
   - Updates share status to `accepted`

3. **Notifications:**
   - Creates notification for sharer (share accepted)
   - Broadcasts real-time notification
   - Logs activity

4. **Response:**
   ```json
   {
     "success": true,
     "message": "Ticket share accepted. 1 ticket is now in your account."
   }
   ```

### 3. Decline Ticket Share
**Route:** `POST /api/tickets/share/{id}/decline`
**Controller:** `App\Http\Controllers\Api\EventTicketShareController@decline`

**Logic Flow:**
1. **Validation:**
   - Finds share record by ID
   - Verifies recipient is authenticated user
   - Checks status is `pending`

2. **Update Status:**
   - Updates share status to `declined`
   - Ticket remains with original owner

3. **Notifications:**
   - Creates notification for sharer (share declined)
   - Broadcasts real-time notification
   - Logs activity

4. **Response:**
   ```json
   {
     "success": true,
     "message": "Ticket share declined."
   }
   ```

### 4. Get Received Shares
**Route:** `GET /api/tickets/shares/received`
**Controller:** `App\Http\Controllers\Api\EventTicketShareController@getReceivedShares`

**Returns:**
- All pending, non-expired shares received by authenticated user
- Includes event details, sharer info, quantity, message, expiration

## Notification System

### Custom Notification Model
Uses `App\Models\Notification` (not Laravel's default notifications table)

**Notification Types:**
- `type`: `ticket`
- `action`: `share_request`, `share_accepted`, `share_declined`
- `reference_id`: Share record ID
- `data`: JSON with share details (event_title, sharer_name, etc.)

### Notification Service
Uses `App\Services\NotificationService::sendUserNotification()` for real-time broadcasting

## Business Rules

### Sharing Restrictions:
1. **One ticket per share:** Each share record represents one ticket
2. **No duplicate shares:** Cannot share same ticket while pending share exists
3. **Expiration:** Shares expire after 7 days
4. **Self-sharing:** Cannot share tickets with yourself
5. **Available tickets only:** Only tickets without pending shares can be shared

### Acceptance Rules:
1. **Ownership transfer:** Ticket ownership transfers to recipient upon acceptance
2. **Tracking:** Original sharer tracked in `shared_by_user_id`
3. **One-time transfer:** Once accepted, ticket cannot be shared again by new owner
4. **Status check:** Only pending shares can be accepted

### Decline Rules:
1. **No transfer:** Ticket stays with original owner
2. **Can re-share:** Declined shares allow ticket to be shared again
3. **Status update:** Share status set to `declined`

## Activity Logging

### EventActivityLog
Logs ticket sharing activities:
- `ticket_share_created` - When share request is created
- `ticket_share_accepted` - When share is accepted
- `ticket_share_declined` - When share is declined

### UserActivityLog
Logs user-level activities for ticket sharing

## Database Relationships

### EventTicketShare Relationships:
- `event()` → BelongsTo Event
- `sharer()` → BelongsTo User (sharer)
- `recipient()` → BelongsTo User (recipient)
- `sharedTicket()` → BelongsTo EventTicketInformation

### EventTicketInformation Relationships:
- `shares()` → HasMany EventTicketShare (via `shared_ticket_id`)

## Scopes

### EventTicketShare Scopes:
- `active()` - Pending and not expired
- `receivedBy($userId)` - Shares received by user
- `sharedBy($userId)` - Shares made by user

## Frontend Integration

### User Search
- API: `GET /api/users/search` - Search users for sharing
- Used in ticket sharing UI to find recipients

### Real-time Updates
- Uses Laravel broadcasting for real-time notifications
- NotificationService handles WebSocket/SSE updates

## Error Handling

### Common Errors:
1. **No available tickets:** 400 - "You do not have any available tickets to share"
2. **Insufficient tickets:** 400 - "You only have X ticket(s) available"
3. **Share not found:** 404 - "Ticket share not found"
4. **Unauthorized:** 403 - "Unauthorized access"
5. **Already processed:** 400 - "This share cannot be accepted (already processed)"
6. **Expired:** 400 - "This share cannot be accepted (expired)"

## Security Considerations

1. **Authentication:** All endpoints require authentication
2. **Authorization:** Recipients can only accept/decline their own shares
3. **Validation:** Strict validation on all inputs
4. **Transaction Safety:** Uses database transactions for consistency
5. **Relationship Loading:** Eager loads relationships to prevent N+1 queries

## Future Enhancements

Potential improvements:
- Bulk sharing (multiple tickets at once)
- Share cancellation by sharer
- Share history tracking
- Expiration reminders
- Share limits per user/event


# Unused Tables and Models Analysis

Based on codebase analysis, here are the unused/unnecessary tables and models:

## Confirmed Unused (No Migration/Table Found)

### 1. **UserOrganizerRole** Model
- **Status**: Model exists but is completely empty (no code)
- **Migration**: No migration found for `user_organizer_roles` table
- **Reason**: Replaced by `EventStaff` table
- **Recommendation**: ✅ **DELETE** - Model file can be removed

### 2. **EventRating** Model  
- **Status**: Model exists but is completely empty (no code)
- **Migration**: No migration found for `event_ratings` table
- **Reason**: Appears to be unused/not implemented
- **Recommendation**: ✅ **DELETE** - Model file can be removed

### 3. **event_registrations** Table
- **Status**: Model exists (`EventRegistration`) but **NO migration found**
- **Usage**: Referenced in `EventController::register()` but migration doesn't exist
- **Reason**: Possibly replaced by `TicketPurchase` system
- **Recommendation**: ⚠️ **INVESTIGATE** - Check if `EventController::register()` is actually used

---

## Legacy/Duplicate Models (Aliases)

### 4. **TicketInformation** Model
- **Status**: Legacy alias pointing to `event_ticket_information` table
- **Actual Table**: `event_ticket_information` (used by `EventTicketInformation` model)
- **Usage**: Not used directly in codebase
- **Recommendation**: ✅ **DELETE** - Use `EventTicketInformation` instead

### 5. **PayoutRequest** Model
- **Status**: Duplicate alias pointing to `event_payout_requests` table
- **Actual Table**: `event_payout_requests` (used by `EventPayoutRequest` model)
- **Usage**: Not used directly in codebase
- **Recommendation**: ✅ **DELETE** - Use `EventPayoutRequest` instead

### 6. **TicketShare** Model
- **Status**: Legacy alias pointing to `event_ticket_shares` table
- **Actual Table**: `event_ticket_shares` (used by `EventTicketShare` model)
- **Usage**: Still used in `MyTicketsController` (but could be replaced)
- **Recommendation**: ⚠️ **REPLACE** - Update `MyTicketsController` to use `EventTicketShare`, then delete

### 7. **EventTicketType** Model
- **Status**: Legacy alias pointing to `event_tickets` table
- **Actual Table**: `event_tickets` (used by `EventTicket` model)
- **Usage**: Still used in `EventController` and `EventRegistration` model
- **Recommendation**: ⚠️ **CONSOLIDATE** - Replace usages with `EventTicket`, then delete

---

## Potentially Unused Tables

### 8. **event_payouts** Table
- **Status**: Legacy table replaced by `event_payout_requests`
- **Migration**: `2025_10_10_120000_create_event_payouts_table.php`
- **Usage**: Used in `EventPayoutController::request()` and `collect()` methods (legacy code)
- **Current System**: `EventPayoutRequest` is used in `show()` method
- **Recommendation**: ⚠️ **INVESTIGATE** - Check if `request()` and `collect()` methods are actually called, if not, migrate to `EventPayoutRequest` and drop table

---

## Summary

### Safe to Delete Now:
1. ✅ `app/Models/UserOrganizerRole.php` - Empty model, no table
2. ✅ `app/Models/EventRating.php` - Empty model, no table  
3. ✅ `app/Models/TicketInformation.php` - Legacy alias, not used
4. ✅ `app/Models/PayoutRequest.php` - Duplicate alias, not used

### Need Investigation/Refactoring:
5. ⚠️ `app/Models/TicketShare.php` - Replace usage in `MyTicketsController` first
6. ⚠️ `app/Models/EventTicketType.php` - Replace usage in `EventController` and `EventRegistration` first
7. ⚠️ `app/Models/EventRegistration.php` - Check if `EventController::register()` is used
8. ⚠️ `app/Models/EventPayout.php` + `event_payouts` table - Check if `request()`/`collect()` methods are called

---

## Next Steps

1. **Immediate**: Delete the 4 safe-to-delete models listed above
2. **Refactor**: Update `MyTicketsController` to use `EventTicketShare` instead of `TicketShare`
3. **Refactor**: Update `EventController` and `EventRegistration` to use `EventTicket` instead of `EventTicketType`
4. **Investigate**: Check if `EventController::register()` route is actually used
5. **Investigate**: Check if `EventPayoutController::request()` and `collect()` methods are called via routes
6. **Migrate**: If `event_payouts` is unused, migrate any remaining data to `event_payout_requests` and drop the table


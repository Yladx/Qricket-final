# Role-Based Access Control Test Cases for Events

| Test Case ID | Test Case | Expected Result | Actual Result |
|-------------|-----------|----------------|---------------|
| **RBA-1** | Event Owner views/edits own event | Full access to all features | |
| **RBA-2** | Admin views/edits any event | Full access to all events | |
| **RBA-3** | Admin creates event (user_id = null) | Event created, admin has full access | |
| **RBA-4** | Staff with `can_manage_event` edits event | Can edit Basic Info, Layout, Certificate | |
| **RBA-5** | Staff with `can_manage_staff` manages staff | Can add/remove/modify staff members | |
| **RBA-6** | Staff with `can_manage_news` creates/edits news | Can manage news, cannot edit event | |
| **RBA-7** | Staff with `can_manage_attendance` checks in attendees | Can check-in and generate certificates | |
| **RBA-8** | Staff with `can_manage_sales` views/exports sales | Can view sales reports and export data | |
| **RBA-9** | Staff with `can_manage_ticket` manages tickets | Can create/edit/delete ticket types | |
| **RBA-10** | Staff with role "organizer" accesses event | Full access (equivalent to owner) | |
| **RBA-11** | Staff with no permissions accesses event | 403 Forbidden - "Unauthorized access" | |
| **RBA-12** | Staff views non-assigned event | 403 Forbidden - "Not assigned to this event" | |
| **RBA-13** | Regular user accesses event | 403 Forbidden - "Unauthorized access" | |
| **RBA-14** | Unauthenticated user accesses event | 401 Unauthorized | |
| **RBA-15** | Staff with `can_manage_news` edits event | 403 Forbidden - "No permission" | |
| **RBA-16** | Staff with `can_manage_attendance` views sales | 403 Forbidden - "Unauthorized" | |
| **RBA-17** | Staff with `can_manage_sales` edits event | 403 Forbidden - "No permission" | |
| **RBA-18** | Event Owner deletes event (draft) | Event deleted successfully | |
| **RBA-19** | Event Owner deletes event (published) | Deletion blocked or confirmation required | |
| **RBA-20** | User modifies event ID in URL to access another event | 403 Forbidden - "Unauthorized access" | |
| **RBA-21** | Staff accesses event after being removed | 403 Forbidden - "Not assigned to this event" | |
| **RBA-22** | Staff accesses event after permissions revoked | 403 Forbidden - "No permission for this action" | |

---

## Permission Hierarchy

1. **Admin** - Full access to all events
2. **Event Owner** - Full access to own events  
3. **Staff (organizer role)** - Full access
4. **Staff (can_manage_event)** - Can edit event
5. **Staff (can_manage_staff)** - Can manage staff + edit event
6. **Staff (specific permissions)** - Access only to assigned features
7. **Staff (no permissions)** - No access (403)
8. **Regular users** - No access (403)

## Expected Status Codes

- **200 OK** - Success
- **401** - Not authenticated
- **403** - Not authorized
- **404** - Not found

---

**Total Test Cases: 22**
**Version: 3.0 (Simplified)**

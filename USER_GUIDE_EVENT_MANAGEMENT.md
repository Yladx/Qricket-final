# User Guide: Event Management Features

## Table of Contents
1. [Event News](#1-event-news)
2. [Feedback Management](#2-feedback-management)
3. [Attendance Management](#3-attendance-management)
4. [Sales Reports](#4-sales-reports)
5. [Organizer Dashboard Overview](#5-organizer-dashboard-overview)
6. [Role-Based Access Control (Staff Permissions)](#6-role-based-access-control-staff-permissions)

---

## 1. Event News

### 1.1 Overview
Event News allows organizers to create and publish announcements, updates, and important information for their event attendees. News articles can be published immediately, saved as drafts, or scheduled for future publication.

### 1.2 Accessing Event News
- Navigate to your event's management page
- Click on **"News"** in the event management menu
- You must have `can_manage_news` permission or be the event owner

### 1.3 Creating News Articles

**Step-by-Step:**
1. Click **"New Announcement"** button on the News page
2. Fill in the form:
   - **Title**: Required, max 255 characters (profanity filtered)
   - **Body**: Required, rich text content (profanity filtered)
   - **Visibility**: Choose who can see this news:
     - **Public**: Visible to everyone viewing the event
     - **Ticket Buyers**: Only visible to users who purchased tickets
     - **Saved Users**: Only visible to users who saved the event
   - **Publish Option**:
     - **Draft**: Save for later editing
     - **Publish**: Publish immediately
     - **Schedule**: Set a future publish date/time
   - **Scheduled At**: Required if "Schedule" is selected (must be in the future)
   - **Images**: Optional, upload up to multiple images (JPEG, JPG, PNG, WEBP, max 8MB each)
3. Click **"Create News Article"**

### 1.4 Managing News Articles

**Viewing News:**
- All news articles are listed on the News page, sorted by latest first
- Each article shows:
  - Title and preview
  - Visibility setting
  - Publish status (Draft/Published/Scheduled)
  - Created date
  - Actions menu (Edit/Delete/Publish)

**Editing News:**
1. Click the **three-dot menu** (⋮) on any news article
2. Select **"Edit"**
3. Make your changes
4. Click **"Update News Article"**

**Publishing Drafts:**
1. Click the **three-dot menu** on a draft article
2. Select **"Publish"**
3. The article will be published immediately and notifications sent

**Deleting News:**
1. Click the **three-dot menu** on any article
2. Select **"Delete"**
3. Confirm deletion

### 1.5 Notifications
When a news article is published:
- **In-app notifications** are sent to:
  - All users who saved the event
  - All users who purchased tickets for the event
- **Email notifications** are sent to the same users
- **Real-time notifications** appear instantly via Pusher

### 1.6 Image Management
- Images are stored in R2 Cloudflare Storage
- Images are accessible via signed URLs (valid for 1 hour)
- Images are automatically regenerated when viewing the news list
- Maximum file size: 8MB per image
- Supported formats: JPEG, JPG, PNG, WEBP

---

## 2. Feedback Management

### 2.1 Overview
Feedback Management allows organizers to view, analyze, and manage feedback submissions from event attendees. Feedback is only available after the event has ended.

### 2.2 Accessing Feedback Management
- Navigate to your event's management page
- Click on **"Feedback"** in the event management menu
- You must have `can_manage_event` permission or be the event owner
- Feedback is only available after the event's `end_date` has passed

### 2.3 Viewing Feedback Statistics

**Dashboard Cards:**
- **Submissions**: Total number of feedback submissions received
- **Attendees**: Total number of unique attendees (users with paid tickets)
- **Response Rate**: Percentage of attendees who submitted feedback
- **Average Rating**: Overall average rating across all categories

**Average Ratings by Category:**
The system calculates average ratings for:
- **Overall Experience**: Experience, Expectations, Organization, Attend Again, Recommend
- **Event Content**: Relevance, Quality, Speakers/Presenters, Materials
- **Venue & Logistics**: Location, Facilities, Accessibility, Catering
- **Value & Satisfaction**: Value for Money, Met Expectations, Overall Satisfaction

Each category shows:
- Overall average rating
- Subcategory breakdowns
- Number of responses per subcategory

### 2.4 Filtering Feedback

**Filter Options:**
- **All**: Show all feedback submissions
- **With Liked Comments**: Only show feedback with "What did you like?" comments
- **With Improvement Comments**: Only show feedback with "What could be improved?" comments

**Viewing Individual Feedback:**
- Click on any feedback submission to view:
  - User information (name, email)
  - All rating responses (by category)
  - Text comments (liked/improvements)
  - Submission date

### 2.5 Requesting Feedback from Attendees

**How to Request:**
1. Ensure your event has ended (`end_date` is in the past)
2. Go to the Feedback Management page
3. Click **"Request Feedback"** button
4. The system will:
   - Send email notifications to all attendees
   - Create in-app notifications for all attendees
   - Trigger event end notifications
   - Mark the event as `feedback_notified_at`

**Who Receives Requests:**
- All users who have paid tickets for the event
- Only users with valid `EventTicketInformation` records linked to paid purchases

**Note:** You can only request feedback once per event (the button disappears after the first request).

### 2.6 Advanced Analytics Access

**For Non-Advanced Analytics Subscribers:**
- You can view individual feedback comments (liked/improvements)
- All feedback data is visible, including text comments

**For Advanced Analytics Subscribers:**
- Additional detailed analytics and insights
- Enhanced filtering and export capabilities

---

## 3. Attendance Management

### 3.1 Overview
Attendance Management allows organizers to track and manage event attendance through manual check-in and QR code scanning. You can view attendance statistics, check in attendees, and generate certificates.

### 3.2 Accessing Attendance Management
- Navigate to your event's management page
- Click on **"Attendance"** in the event management menu
- You must have `can_manage_attendance` permission or be the event owner

### 3.3 Attendance Statistics

**Overview Cards:**
- **Checked-In Tickets**: Number of tickets that have been checked in
- **Total Tickets**: Total number of paid tickets for the event
- **Attendance Rate**: Percentage of tickets checked in

### 3.4 Checking In Attendees

**Manual Check-In:**
1. Find the attendee in the attendance list
2. Click the **"Check In"** button next to their name
3. The attendee is immediately marked as checked in
4. A notification is sent to the attendee

**QR Code Scanning:**
1. Use the **QR Code Scanner** (available in the scanner page)
2. Scan the attendee's ticket QR code
3. The system automatically:
   - Validates the ticket
   - Checks if already checked in
   - Marks as checked in if valid
   - Sends notification to attendee

**Manual Ticket ID Entry:**
1. Enter the ticket ID manually (format: `{event_id}_{ticket_id}` or just `{ticket_id}`)
2. Click **"Check In"**
3. The system processes the check-in

### 3.5 Viewing Attendance List

**Ticket Information Displayed:**
- User name and email
- Profile picture
- Ticket type
- Purchase ID
- Ticket ID
- Check-in status (Checked In / Not Checked In)
- Check-in date/time (if checked in)
- Certificate status (if certificates are enabled)

**Filtering Options:**
- **All Tickets**: Show all paid tickets
- **Checked In**: Show only checked-in tickets
- **Not Checked In**: Show only pending check-ins
- **By Ticket Type**: Filter by specific ticket type

**Search:**
- Search by user name or email
- Search by ticket ID or purchase ID

### 3.6 Certificate Generation

**Prerequisites:**
- Event must have certificates enabled
- Attendees must be checked in
- Certificates must be configured in the Certificate section

**Generating Certificates:**
1. Click **"Generate Certificates"** button
2. The system will:
   - Generate certificates for all checked-in attendees who haven't received them
   - Mark certificates as "given" in the database
   - Send email notifications with certificate links to all recipients
3. You'll see a summary:
   - Number of certificates generated
   - Number of emails sent
   - Any errors encountered

**Certificate Delivery:**
- Certificates are sent via email with a direct link
- Each attendee receives a unique certificate URL
- Certificates are accessible via: `/events/{event_id}/certificate/public?ticket={ticket_id}`

### 3.7 Exporting Attendance Data

**Export Options:**
- Export attendance list to Excel (XLSX format)
- Includes all ticket information, check-in status, and timestamps
- Useful for record-keeping and reporting

---

## 4. Sales Reports

### 4.1 Overview
Sales Reports provide detailed information about ticket sales, revenue, fees, and financial statistics for your event. Reports update in real-time and can be exported for accounting purposes.

### 4.2 Accessing Sales Reports
- Navigate to your event's management page
- Click on **"Sales"** in the event management menu
- You must have access to view sales (owner or staff with appropriate permissions)

### 4.3 Sales Report Dashboard

**Summary Statistics:**
- **Total Orders**: Number of ticket purchase transactions
- **Total Amount**: Sum of all ticket sales (before fees)
- **Net Amount**: Total after Xendit gateway fees are deducted
- **Total Fees**: Sum of transaction fees and VAT
- **Expected Earnings**: Net amount you'll receive (after Qricket service fee)
- **Free Tickets Sold**: Count of free tickets sold

**Real-Time Updates:**
- Sales data updates in real-time via WebSocket connections
- Connection indicator shows green when connected, red when disconnected
- Statistics update automatically as new sales occur

### 4.4 Sales Data Table

**Columns Displayed:**
- **Buyer**: Customer name
- **Purchase ID**: Unique purchase identifier
- **Ticket**: Ticket type name
- **Status**: Payment status (Paid, Pending, Refunded, Cancelled, Failed)
- **Quantity**: Number of tickets purchased
- **Total**: Total amount paid by customer
- **Payment Method**: Payment method used (e-wallet, bank transfer, etc.)
- **Transaction Fee**: Xendit gateway fee deducted
- **VAT**: Value-added tax amount
- **Net Amount**: Amount after fees (what organizer receives)
- **Paid At**: Date and time of payment

### 4.5 Filtering and Searching Sales

**Filter Options:**
- **Search**: Search by buyer name or email
- **Status**: Filter by payment status (All, Paid, Pending, Refunded, Cancelled, Failed)
- **Date From**: Filter sales from a specific date
- **Date To**: Filter sales up to a specific date
- **Sort By**: 
  - Paid Date (default)
  - Total Amount
  - Quantity
- **Sort Direction**: Ascending or Descending

**Using Filters:**
1. Enter your filter criteria
2. Click **"Filter"** button
3. Results update immediately
4. Click **"Reset"** to clear all filters

### 4.6 Exporting Sales Data

**Export to Excel:**
1. Click **"Export XLS"** button
2. The system generates an Excel file with:
   - All sales data (respecting current filters)
   - All columns from the table
   - Formatted for easy analysis
3. File downloads automatically
4. Use for accounting, tax reporting, or analysis

**Export Includes:**
- All filtered sales records
- Complete financial breakdown
- Buyer information
- Payment details
- Timestamps

### 4.7 Understanding Financial Breakdown

**Total Amount:**
- The full amount paid by the customer
- This is what appears on the invoice

**Transaction Fee:**
- Xendit gateway processing fee
- Deducted automatically by Xendit
- Varies by payment method

**VAT:**
- Value-added tax (if applicable)
- Calculated based on transaction amount

**Net Amount:**
- Total Amount - Transaction Fee - VAT
- This is what is stored in the `net_amount` field
- This is the base for payout calculations

**Expected Earnings:**
- Net Amount - Qricket Service Fee (5% default)
- This is what you'll receive in payouts
- Qricket fee is calculated on the net amount (after Xendit fees)

---

## 5. Organizer Dashboard Overview

### 5.1 Overview
The Organizer Dashboard provides a comprehensive overview of all your events, including statistics, recent activity, upcoming events, and sales summaries.

### 5.2 Accessing the Dashboard
- Navigate to **Organizer → Dashboard** (or click "Dashboard" in the organizer navigation)
- The dashboard shows events you own or where you're assigned as staff

### 5.3 View Filters

**View Options:**
- **All**: Shows all events (events you own + events where you're staff)
- **Owner**: Shows only events you created
- **Staff**: Shows only events where you're assigned as staff (must be published)

**Switching Views:**
- Use the view filter dropdown at the top of the dashboard
- Each view shows different event sets based on your role

### 5.4 Dashboard Statistics

**Event Statistics:**
- **Total Events**: Count of all events in the current view
- **Active Events**: Number of published events
- **Draft Events**: Number of draft events
- **Upcoming Events**: Number of events with `start_date` in the future

**Sales Statistics** (only visible if you can manage sales):
- **Total Revenue**: Sum of all sales across your events
- **Total Tickets Sold**: Count of all tickets sold
- **Average Ticket Price**: Average price per ticket
- **Top Performing Event**: Event with highest revenue

### 5.5 Recent Activity

**Activity Feed:**
- Shows the 10 most recent activity logs across all your events
- Includes:
  - Event name
  - Action performed (created, updated, published, etc.)
  - User who performed the action
  - Timestamp

**Activity Types:**
- Event creation
- Event updates
- Status changes
- Ticket sales
- Staff assignments
- News publications

### 5.6 Upcoming Events

**Upcoming Events List:**
- Shows the 5 nearest upcoming events
- Displays:
  - Event title
  - Start date and time
  - Location
  - Status
- Sorted by start date (earliest first)

### 5.7 Top Events by Revenue

**Revenue Rankings:**
- Shows events ranked by total revenue
- Displays:
  - Event title
  - Total revenue amount
  - Number of tickets sold
  - Event status
- Only shows events you have access to view sales for

### 5.8 Recent Sales

**Recent Sales Feed:**
- Shows the most recent ticket purchases across your events
- Displays:
  - Event name
  - Buyer name
  - Purchase amount
  - Purchase date
- Updates in real-time

### 5.9 Revenue Trend Chart

**Chart Display:**
- Visual representation of revenue over time
- Shows revenue trends for the selected time period
- Helps identify peak sales periods
- Only visible if you can manage sales

### 5.10 Calendar View

**Event Calendar:**
- Shows events scheduled for the current month
- Displays:
  - Event title
  - Date and time
  - Location
- Click on events to navigate to event details
- Only shows published events

---

## 6. Role-Based Access Control (Staff Permissions)

### 6.1 Overview
Qricket supports role-based access control for event staff. Event owners can assign staff members with specific permissions to help manage events without granting full access.

### 6.2 Staff Roles

**Available Roles:**
- **Organizer**: Full access (same as owner, but assigned as staff)
- **Administrator**: High-level access with most management permissions
- **Manager**: Mid-level access for day-to-day operations
- **Support**: Limited access for customer support tasks

### 6.3 Permission Types

**Available Permissions:**
- **Can Manage Event**: Full event editing access (Basic Info, Layout, Certificate)
- **Can Manage Staff**: Ability to add/remove staff members
- **Can Manage News**: Create, edit, delete, and publish news articles
- **Can Manage Attendance**: Check in attendees, view attendance, generate certificates
- **Can Manage Sales**: View sales reports, export data, view financial information
- **Can Manage Ticket**: Manage ticket types, pricing, and availability

### 6.4 Permission-Based Access

**Event Management Access:**
- **With `can_manage_event`**: Can edit event details (Basic Info, Layout, Certificate)
- **Without `can_manage_event`**: Can only view event info (read-only)

**News Management:**
- **With `can_manage_news` OR role is "organizer"**: Can create, edit, delete, and publish news
- **Without permission**: Cannot access news management

**Attendance Management:**
- **With `can_manage_attendance`**: Can check in attendees, view attendance, generate certificates
- **Without permission**: Cannot access attendance features

**Sales Reports:**
- **With `can_manage_sales`**: Can view sales reports, export data, see financial breakdowns
- **Without permission**: Cannot access sales reports

**Ticket Management:**
- **With `can_manage_ticket`**: Can create, edit, and manage ticket types
- **Without permission**: Cannot modify ticket settings

**Staff Management:**
- **With `can_manage_staff`**: Can add, remove, and modify staff members
- **Without permission**: Cannot manage staff assignments

### 6.5 Default Permissions by Role

**Organizer Role:**
- All permissions enabled by default
- Equivalent to event owner access

**Administrator Role:**
- Typically has: `can_manage_event`, `can_manage_news`, `can_manage_attendance`, `can_manage_sales`
- May or may not have: `can_manage_staff`, `can_manage_ticket`
- Set by event owner when assigning

**Manager Role:**
- Typically has: `can_manage_news`, `can_manage_attendance`
- Usually does NOT have: `can_manage_event`, `can_manage_staff`
- Set by event owner when assigning

**Support Role:**
- Typically has: `can_manage_attendance` (for check-ins)
- Usually does NOT have: `can_manage_event`, `can_manage_sales`, `can_manage_staff`
- Set by event owner when assigning

### 6.6 Access Control Examples

**Example 1: Event Owner**
- Full access to all features
- Can assign staff
- Can manage all aspects of the event

**Example 2: Staff with `can_manage_news` only**
- Can create and publish news articles
- Can view event info (read-only)
- Cannot edit event details
- Cannot view sales or attendance

**Example 3: Staff with `can_manage_attendance` only**
- Can check in attendees
- Can view attendance list
- Can generate certificates
- Cannot edit event or view sales

**Example 4: Staff with `can_manage_sales` only**
- Can view sales reports
- Can export sales data
- Cannot edit event or manage attendance

### 6.7 Viewing Your Permissions

**As Staff Member:**
- Your accessible features are automatically filtered based on your permissions
- If you don't have permission, menu items and buttons won't appear
- Attempting to access restricted features will show a 403 Forbidden error

**As Event Owner:**
- You can see all staff members and their permissions
- Go to Event → Staff Management to view assignments
- You can modify permissions when editing staff assignments

### 6.8 Best Practices

**Security Tips:**
- Only grant necessary permissions to staff members
- Use the principle of least privilege
- Regularly review staff permissions
- Remove staff access when no longer needed

**Common Permission Combinations:**
- **Event Coordinator**: `can_manage_event`, `can_manage_news`, `can_manage_attendance`
- **Sales Manager**: `can_manage_sales`, `can_manage_ticket`
- **Support Staff**: `can_manage_attendance` only
- **Content Manager**: `can_manage_news` only

---

## Summary

This guide covers the core event management features in Qricket:
- **Event News**: Create and publish announcements to keep attendees informed
- **Feedback Management**: Collect and analyze attendee feedback after events
- **Attendance Management**: Track check-ins and generate certificates
- **Sales Reports**: Monitor revenue, fees, and financial performance
- **Dashboard Overview**: Get a comprehensive view of all your events
- **Role-Based Access**: Assign staff with specific permissions for secure collaboration

All features respect role-based access control, ensuring that staff members only see and can manage features they're authorized to access.


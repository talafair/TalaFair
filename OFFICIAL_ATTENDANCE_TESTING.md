# Official Attendance Support - Testing Guide

## Overview
This document outlines the comprehensive testing scenarios for the new official attendance feature in TalaFair.

## Test Scenarios

### 1. OFFICIAL SELF-SCANNING
**Scenario:** Official attends an event and records their own attendance via Event QR

**Steps:**
- Login as an Official
- Navigate to Attendance Scanner
- Select "My Attendance" tab
- Select an event
- Tap "Open camera"
- Scan the Event QR code
- Verify attendance is recorded

**Expected Outcomes:**
✓ Attendance record created with user_category = 'official'
✓ Attendance method = 'event_qr_scan'
✓ No points awarded (breakdown.total = 0)
✓ Official NOT added to raffle pool
✓ Success message: "Attendance recorded successfully."

**Verify in Database:**
```sql
SELECT * FROM attendances 
WHERE user_id = [official_id] 
AND user_category = 'official' 
AND attendance_method = 'event_qr_scan';
```

---

### 2. OFFICIAL SELF-SCANNING - DUPLICATE PREVENTION
**Scenario:** Official tries to scan the same event QR twice

**Steps:**
1. Complete Scenario 1 (first scan)
2. Scan the same Event QR again
3. Verify error message

**Expected Outcomes:**
✓ Second scan rejected
✓ Error message: "Your attendance has already been recorded for this event."
✓ No duplicate attendance record created

**Verify in Database:**
```sql
SELECT COUNT(*) as attendance_count 
FROM attendances 
WHERE user_id = [official_id] 
AND announcement_id = [event_id];
-- Result should be 1, not 2
```

---

### 3. OFFICIAL RECORDING RESIDENT ATTENDANCE - RESIDENT QR
**Scenario:** Official records resident's attendance by scanning resident QR

**Steps:**
- Login as Official
- Navigate to Attendance Scanner
- Select "Record Resident" tab
- Select an event
- Tap "Open camera"
- Scan a Resident QR code
- Verify attendance is recorded

**Expected Outcomes:**
✓ Attendance record created with user_category = 'resident'
✓ Attendance method = 'official_qr_scan'
✓ Points awarded to resident
✓ Resident added to raffle pool
✓ Success message shows resident name and points

**Verify in Database:**
```sql
SELECT * FROM attendances 
WHERE user_id = [resident_id] 
AND user_category = 'resident' 
AND attendance_method = 'official_qr_scan';
```

---

### 4. OFFICIAL RECORDING RESIDENT ATTENDANCE - MANUAL ID
**Scenario:** Official records resident's attendance by manually entering Unique ID

**Steps:**
- Login as Official
- Navigate to Attendance Scanner
- Select "Record Resident" tab
- Select an event
- Click "QR code not scanning? Enter Resident Unique ID instead"
- Enter resident's Unique ID (e.g., Z2-26-000000001)
- Tap "Record attendance"
- Verify attendance is recorded

**Expected Outcomes:**
✓ Attendance record created with user_category = 'resident'
✓ Attendance method = 'manual_unique_id'
✓ Points awarded to resident
✓ Resident added to raffle pool
✓ Success message shows resident name and points

**Verify in Database:**
```sql
SELECT * FROM attendances 
WHERE user_id = [resident_id] 
AND user_category = 'resident' 
AND attendance_method = 'manual_unique_id';
```

---

### 5. RESIDENT SELF-SCANNING
**Scenario:** Resident records their own attendance via Event QR

**Steps:**
- Login as Resident
- Navigate to Attendance Scanner
- Tap "Open camera"
- Scan the Event QR code
- Verify attendance is recorded

**Expected Outcomes:**
✓ Attendance record created with user_category = 'resident'
✓ Attendance method = 'event_qr_scan'
✓ Points awarded to resident
✓ Resident added to raffle pool
✓ UI shows points breakdown

**Verify in Database:**
```sql
SELECT * FROM attendances 
WHERE user_id = [resident_id] 
AND user_category = 'resident' 
AND attendance_method = 'event_qr_scan'
AND points_awarded > 0;
```

---

### 6. DUPLICATE PREVENTION - RESIDENT
**Scenario:** Resident tries to scan the same event QR twice

**Steps:**
1. Complete Scenario 5 (first scan)
2. Scan the same Event QR again
3. Verify error message

**Expected Outcomes:**
✓ Second scan rejected
✓ Error message: "Your attendance has already been recorded for this event."
✓ No duplicate attendance record
✓ No additional points awarded

**Verify in Database:**
```sql
SELECT COUNT(*) as attendance_count 
FROM attendances 
WHERE user_id = [resident_id] 
AND announcement_id = [event_id];
-- Result should be 1
```

---

### 7. POINTS VERIFICATION
**Scenario:** Verify official receives 0 points, resident receives points

**Setup:**
- Create an event with base_points = 100
- Official and Resident both scan
- Compare points awarded

**Expected:**
- Official attendance: points_awarded = 0
- Resident attendance: points_awarded = 100

**Verify in Database:**
```sql
SELECT user_id, user_category, points_awarded 
FROM attendances 
WHERE announcement_id = [event_id]
ORDER BY user_category DESC;
```

---

### 8. RAFFLE ELIGIBILITY VERIFICATION
**Scenario:** Verify officials are NOT in raffle pool

**Setup:**
- Event has raffle enabled (raffle_enabled = 1)
- Both official and resident attend

**Expected:**
- EventRaffleEntry created for resident
- EventRaffleEntry NOT created for official

**Verify in Database:**
```sql
SELECT user_id, user_category 
FROM event_raffle_entries 
WHERE announcement_id = [event_id];
-- Should only show resident, not official
```

---

### 9. ATTENDANCE STATISTICS
**Scenario:** Statistics page shows correct separation of residents/officials

**Setup:**
- Event with 3 residents and 2 officials attending

**Expected on Statistics Page:**
- Residents present: 3
- Officials present: 2
- Total checked in: 5

**Verify in Database:**
```sql
SELECT 
    user_category,
    COUNT(*) as count
FROM attendances 
WHERE announcement_id = [event_id]
GROUP BY user_category;
```

---

### 10. EARLY BONUS FOR RESIDENTS
**Scenario:** Resident scans before event start time

**Setup:**
- Event start time: 2:00 PM
- Current time: 12:30 PM (within 2-hour window, before start)
- Event base_points = 100, confirmation_points = 50

**Expected:**
- Early bonus applied: 10% of base = 10
- Total points = 100 + 50 + 10 = 160
- Attendance.is_early = true

---

### 11. EARLY BONUS NOT FOR OFFICIALS
**Scenario:** Official scans before event start (early), no points awarded

**Setup:**
- Same as Scenario 10
- Official scans instead of resident

**Expected:**
- Attendance.is_early = true
- points_awarded = 0 (not 160)
- No points added to user

---

### 12. SCANNING WINDOW ENFORCEMENT
**Scenario:** Verify scanning only works 2 hours before event

**Setup:**
- Event starts: 2:00 PM

**Test Cases:**
a) **Before window opens (11:55 AM)**
   - Scan attempt
   - Expected: Error "Attendance scanning is not available yet. You may scan starting [time]."

b) **Window open (12:00 PM - 6:00 PM)**
   - Scan attempt
   - Expected: Success

c) **After window closes (6:01 PM)**
   - Scan attempt
   - Expected: Error "Attendance scanning has ended for this event."

---

### 13. MANUAL MODE ONLY IN RESIDENT MODE
**Scenario:** Manual ID entry only available when official is in "Record Resident" mode

**Setup:**
- Login as Official
- Navigate to Attendance Scanner

**Test Cases:**
a) **My Attendance tab selected**
   - "Manual ID" button should not appear OR should show error if clicked
   
b) **Record Resident tab selected**
   - "Manual ID" button should appear
   - Manual form should be functional

---

### 14. ATTENDANCE SHEET PDF
**Scenario:** PDF includes both residents and officials with category shown

**Setup:**
- Generate attendance sheet PDF from statistics page

**Expected in PDF:**
- List includes all attendees (residents + officials)
- User category column visible or noted
- Correct attendance timestamps and methods recorded

---

### 15. DATABASE INTEGRITY
**Scenario:** Verify no orphaned or invalid records

**Checks:**
```sql
-- Check for invalid user_category values
SELECT * FROM attendances 
WHERE user_category NOT IN ('resident', 'official');

-- Check for invalid attendance_method values
SELECT * FROM attendances 
WHERE attendance_method NOT IN ('event_qr_scan', 'official_qr_scan', 'manual_unique_id');

-- Check no official raffle entries
SELECT a.user_category, e.* 
FROM event_raffle_entries e
JOIN attendances a ON e.user_id = a.user_id 
  AND e.announcement_id = a.announcement_id
WHERE a.user_category = 'official';
-- Result should be empty

-- Check all officials have 0 points_awarded
SELECT user_category, points_awarded 
FROM attendances 
WHERE user_category = 'official' 
AND points_awarded > 0;
-- Result should be empty
```

---

## UI/UX Testing

### 16. OFFICIAL MODE UI
**Checklist:**
- [ ] Tab buttons visible and functional
- [ ] "My Attendance" tab shows event selector with clear labeling
- [ ] "Record Resident" tab shows separate event selector
- [ ] Manual ID toggle only visible in "Record Resident" mode
- [ ] Camera icons are clear
- [ ] Success/error messages are distinct and helpful
- [ ] Mobile view is responsive (no horizontal scroll)
- [ ] Text is readable on small screens

### 17. ERROR MESSAGES
**Verify these exact messages appear:**
- [ ] "Your attendance has already been recorded for this event." (duplicate official)
- [ ] "Attendance recorded successfully." (official, when applicable)
- [ ] "Invalid event QR code. Please scan this event's QR code."
- [ ] "Attendance scanning is not available yet. You may scan starting [time]."
- [ ] "Select your event before starting the scanner." (My Attendance mode)
- [ ] "Select the resident's event before starting the scanner." (Record Resident mode)

---

## Performance Testing

### 18. CONCURRENT SCANS
**Scenario:** Multiple officials scan simultaneously

**Expected:**
- No race conditions
- All records created correctly
- No duplicate prevention failures
- All points/raffle logic works correctly

---

## Compatibility Testing

### 19. EXISTING FUNCTIONALITY PRESERVED
**Checklist:**
- [ ] Resident attendance flow unchanged
- [ ] Points calculation for residents still correct
- [ ] Raffle draws from residents only
- [ ] Badges awarded to residents correctly
- [ ] RSVP functionality unaffected
- [ ] Activity participation scanning unaffected
- [ ] QR code generation unchanged
- [ ] ID cards display unchanged

---

## Edge Cases

### 20. EDGE CASES
**Test Cases:**

a) **Official tries resident Unique ID that belongs to an official**
   - Expected: Error "Unique QR ID not found" (officials filtered in query)

b) **Event ends while official is scanning**
   - Expected: Scan still works (within geofence and window)

c) **Event QR expires while official is scanning**
   - Expected: Error about expired QR

d) **Official with no official permissions tries official features**
   - Expected: Proper authorization check (403)

e) **Scan with invalid JSON in QR**
   - Expected: Treated as event QR, not resident QR

---

## Rollback Testing

### 21. ROLLBACK SAFETY
**Scenario:** Rollback migration and verify application still works

**Steps:**
```bash
php artisan migrate:rollback
```

**Expected:**
- Application still works
- attendance table exists without new columns
- No fatal errors
- All data preserved

**To restore:**
```bash
php artisan migrate
```

---

## SQL Query Examples for Verification

### Quick Status Check
```sql
-- Overall attendance summary
SELECT 
    user_category,
    attendance_method,
    COUNT(*) as count,
    SUM(points_awarded) as total_points,
    MAX(scanned_at) as latest_scan
FROM attendances
WHERE announcement_id = [EVENT_ID]
GROUP BY user_category, attendance_method;

-- Resident points verification
SELECT 
    u.name,
    a.user_category,
    a.points_awarded,
    u.points as total_user_points
FROM attendances a
JOIN users u ON a.user_id = u.id
WHERE a.announcement_id = [EVENT_ID]
ORDER BY a.scanned_at;

-- Officials verification
SELECT 
    u.name,
    a.user_category,
    a.attendance_method,
    a.points_awarded,
    a.scanned_at
FROM attendances a
JOIN users u ON a.user_id = u.id
WHERE a.announcement_id = [EVENT_ID]
AND a.user_category = 'official';
```

---

## Sign-off

- [ ] All 21 test scenarios passed
- [ ] No regressions in existing functionality
- [ ] UI is clear and user-friendly
- [ ] Database integrity verified
- [ ] Performance acceptable
- [ ] Ready for production

**Tested by:** ________________
**Date:** ________________
**Notes:** 


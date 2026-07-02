PHASE 8C.1 ESP32 AUTOMATIC ENROLLMENT

This is the updated all-in-one firmware.

Before uploading, edit:
- YOUR_HOTSPOT_NAME
- YOUR_HOTSPOT_PASSWORD
- YOUR_LAPTOP_IP
- CHANGE_THIS_SECRET_KEY_12345

How to enroll fingerprint automatically:
1. Admin opens admin/device_enrollment.php
2. Select student
3. Select Fingerprint
4. Enter fingerprint ID, for example 1
5. Click Send Enrollment Request
6. On ESP32, hold GPIO 20 for 3 seconds
7. In enrollment menu, 1 click = Fingerprint
8. ESP32 fetches pending request
9. Student enrolls finger
10. ESP32 sends fingerprint ID to database automatically

How to enroll RFID automatically:
1. Admin opens admin/device_enrollment.php
2. Select student
3. Select RFID
4. Leave fingerprint ID empty
5. Click Send Enrollment Request
6. On ESP32, hold GPIO 20 for 3 seconds
7. In enrollment menu, 2 clicks = RFID
8. Tap card
9. ESP32 sends RFID UID to database automatically

No copy and paste needed.
No switching firmware needed.

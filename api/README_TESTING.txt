API TESTING FILE

Copy all files inside this api folder into:

htdocs/university_attendance_system/api/

After copying, your api folder should contain:

config.php
test_api.php
get_active_session.php
mark_attendance.php
test_attendance_form.php
README_TESTING.txt

Open this to test the API:

http://localhost/university_attendance_system/api/test_api.php

Open this to test attendance manually:

http://localhost/university_attendance_system/api/test_attendance_form.php

Before using test_attendance_form.php:
1. Login as lecturer.
2. Start an attendance session.
3. Make sure student has fingerprint_id or rfid_uid.
4. Make sure student is registered for that course.

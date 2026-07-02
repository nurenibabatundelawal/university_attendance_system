<?php
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$active_sessions = mysqli_query($conn, "
    SELECT attendance_sessions.id,
           courses.course_code,
           courses.course_title,
           lecturers.fullname AS lecturer_name,
           attendance_sessions.session_date
    FROM attendance_sessions
    JOIN courses ON attendance_sessions.course_id = courses.id
    JOIN lecturers ON attendance_sessions.lecturer_id = lecturers.id
    WHERE attendance_sessions.status = 'Active'
    ORDER BY attendance_sessions.id DESC
");

$students = mysqli_query($conn, "
    SELECT id, matric_no, fullname, fingerprint_id, rfid_uid
    FROM students
    WHERE status = 'Active'
    ORDER BY fullname ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Attendance API</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; }
        .box { max-width: 850px; background: white; margin: 50px auto; padding: 30px; border-radius: 10px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; }
        button { padding: 10px 18px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Test Attendance API</h2>

    <form method="POST" action="mark_attendance.php" target="_blank">
        <label>API Key</label>
        <input type="text" name="api_key" value="<?php echo ESP32_API_KEY; ?>" required>

        <br><br>

        <label>Active Attendance Session</label>
        <select name="session_id" required>
            <option value="">Select Active Session</option>
            <?php while ($session = mysqli_fetch_assoc($active_sessions)) { ?>
                <option value="<?php echo $session['id']; ?>">
                    Session ID <?php echo $session['id']; ?> -
                    <?php echo $session['course_code']; ?> -
                    <?php echo $session['course_title']; ?> -
                    <?php echo $session['lecturer_name']; ?> -
                    <?php echo $session['session_date']; ?>
                </option>
            <?php } ?>
        </select>

        <br><br>

        <label>Identifier Type</label>
        <select name="identifier_type" required>
            <option value="">Select Type</option>
            <option value="fingerprint">Fingerprint</option>
            <option value="rfid">RFID</option>
        </select>

        <br><br>

        <label>Identifier Value</label>
        <input type="text" name="identifier_value" placeholder="Fingerprint ID e.g. 1 OR RFID UID e.g. 04A1BC234D" required>

        <br><br>

        <button type="submit">Send Test Attendance</button>
    </form>

    <h3>Available Students</h3>
    <table>
        <tr><th>Matric No</th><th>Name</th><th>Fingerprint ID</th><th>RFID UID</th></tr>
        <?php while ($student = mysqli_fetch_assoc($students)) { ?>
            <tr>
                <td><?php echo $student['matric_no']; ?></td>
                <td><?php echo $student['fullname']; ?></td>
                <td><?php echo $student['fingerprint_id']; ?></td>
                <td><?php echo $student['rfid_uid']; ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>

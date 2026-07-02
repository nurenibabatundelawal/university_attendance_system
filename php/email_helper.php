<?php

function getEmailSettings($conn) {
    $q = mysqli_query($conn, "SELECT * FROM email_settings LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        return mysqli_fetch_assoc($q);
    }
    return null;
}

function sendEmail($to, $subject, $body, $conn) {
    $settings = getEmailSettings($conn);
    if (!$settings) return false;

    $fromEmail = $settings['smtp_email'];
    $fromName = $settings['from_name'] ?? 'University Attendance System';
    $from = "$fromName <$fromEmail>";

    if (!empty($settings['api_key'])) {
        return sendViaResendApi($to, $subject, $body, $from, $settings['api_key']);
    }

    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    ini_set('SMTP', $settings['smtp_host']);
    ini_set('smtp_port', $settings['smtp_port']);

    return mail($to, $subject, $body, $headers);
}

function sendViaResendApi($to, $subject, $body, $from, $apiKey) {
    $payload = json_encode([
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $body
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}

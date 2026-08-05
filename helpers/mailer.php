<?php
// helpers/mailer.php - Native Pure-PHP Socket SMTP Email Client (No External Libraries Required)

function send_smtp_email($toEmail, $subject, $bodyHtml) {
    $mailConfig = require __DIR__ . '/../config/mail.php';

    $smtpHost = $mailConfig['smtp_host'] ?? 'smtp.gmail.com';
    $smtpPort = $mailConfig['smtp_port'] ?? 587;
    $smtpUser = $mailConfig['smtp_user'] ?? '';
    $smtpPass = $mailConfig['smtp_pass'] ?? '';
    $fromEmail = $mailConfig['from_email'] ?? 'noreply@sahanetpro.com';
    $fromName = $mailConfig['from_name'] ?? 'SahaNet PRO';

    // If no SMTP password provided in config, fallback to local log helper
    if (empty($smtpUser) || empty($smtpPass)) {
        log_local_email($toEmail, $subject, $bodyHtml);
        return [
            'success' => false,
            'reason' => 'config_empty',
            'message' => 'Gmail SMTP kullanıcı adı/şifresi config/mail.php içinde boş olduğu için simüle edildi.'
        ];
    }

    try {
        $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
        if (!$socket) {
            log_local_email($toEmail, $subject, $bodyHtml);
            return ['success' => false, 'reason' => 'connection_failed', 'message' => "SMTP Bağlantı hatası: {$errstr} ({$errno})"];
        }

        read_smtp_response($socket);

        // EHLO Command
        fwrite($socket, "EHLO " . gethostname() . "\r\n");
        read_smtp_response($socket);

        // STARTTLS Command
        fwrite($socket, "STARTTLS\r\n");
        read_smtp_response($socket);

        // Upgrade socket connection to TLS
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);

        // EHLO after TLS
        fwrite($socket, "EHLO " . gethostname() . "\r\n");
        read_smtp_response($socket);

        // AUTH LOGIN
        fwrite($socket, "AUTH LOGIN\r\n");
        read_smtp_response($socket);

        fwrite($socket, base64_encode($smtpUser) . "\r\n");
        read_smtp_response($socket);

        fwrite($socket, base64_encode($smtpPass) . "\r\n");
        $authRes = read_smtp_response($socket);

        if (strpos($authRes, '235') === false) {
            fclose($socket);
            log_local_email($toEmail, $subject, $bodyHtml);
            return ['success' => false, 'reason' => 'auth_failed', 'message' => 'Gmail SMTP Giriş Başarısız: Kullanıcı adı veya şifre hatalı.'];
        }

        // MAIL FROM
        fwrite($socket, "MAIL FROM: <{$fromEmail}>\r\n");
        read_smtp_response($socket);

        // RCPT TO
        fwrite($socket, "RCPT TO: <{$toEmail}>\r\n");
        read_smtp_response($socket);

        // DATA
        fwrite($socket, "DATA\r\n");
        read_smtp_response($socket);

        // MIME Headers & Body
        $headers  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
        $headers .= "To: <{$toEmail}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

        $emailData = $headers . $bodyHtml . "\r\n.\r\n";

        fwrite($socket, $emailData);
        $dataRes = read_smtp_response($socket);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        log_local_email($toEmail, $subject, $bodyHtml);

        return ['success' => true, 'message' => '📧 E-Posta gerçek Gmail sunucusu üzerinden başarıyla iletildi!'];

    } catch (Exception $e) {
        log_local_email($toEmail, $subject, $bodyHtml);
        return ['success' => false, 'reason' => 'exception', 'message' => $e->getMessage()];
    }
}

function read_smtp_response($socket) {
    $response = '';
    while ($str = fgets($socket, 515)) {
        $response .= $str;
        if (substr($str, 3, 1) === ' ') {
            break;
        }
    }
    return $response;
}

function log_local_email($toEmail, $subject, $bodyHtml) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/sent_emails.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] TO: {$toEmail} | SUBJECT: {$subject}\nBODY:\n{$bodyHtml}\n" . str_repeat('-', 80) . "\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

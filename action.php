<?php
    require_once __DIR__ . '/rate_limit.php';
    rateLimit('action', 60, 60);

    require_once __DIR__ . '/sendmail/emailHelper.php';

    // Sanitize inputs
    $name    = htmlspecialchars(trim($_POST['name']    ?? ''), ENT_QUOTES, 'UTF-8');
    $email   = filter_var(trim($_POST['email']   ?? ''), FILTER_SANITIZE_EMAIL);
    $phone   = htmlspecialchars(trim($_POST['phone']   ?? ''), ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
    $msg     = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($msg)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // ── Save to contacts.json ────────────────────────────────────────────────
    $dataPath   = __DIR__ . '/data/contacts.json';
    $contacts   = file_exists($dataPath) ? json_decode(file_get_contents($dataPath), true) : [];
    $maxId      = empty($contacts) ? 0 : max(array_column($contacts, 'id'));
    $contacts[] = [
        'id'      => $maxId + 1,
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'subject' => $subject,
        'message' => $msg,
        'date'    => date('Y-m-d H:i:s'),
        'read'    => false,
    ];
    file_put_contents($dataPath, json_encode($contacts, JSON_PRETTY_PRINT), LOCK_EX);

    // ── Send emails ──────────────────────────────────────────────────────────
    $tableMsg = '<table border="1" cellpadding="10" cellspacing="0">
                    <tr><th>Name</th><td>' . $name . '</td></tr>
                    <tr><th>Email</th><td>' . $email . '</td></tr>
                    <tr><th>Phone</th><td>' . $phone . '</td></tr>
                    <tr><th>Subject</th><td>' . $subject . '</td></tr>
                    <tr><th>Message</th><td>' . nl2br($msg) . '</td></tr>
                </table>';

    $mail = sendMail($email, $tableMsg, $subject);
    if (!empty($mail)) {
        $res = responseMail($email, $name);
        if (!empty($res)) {
            echo json_encode(['status' => 'success', 'message' => 'success']);
        }
    } else {
        // Email failed but message was saved to JSON — still return success
        echo json_encode(['status' => 'success', 'message' => 'success']);
    }
?>

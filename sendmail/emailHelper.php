<?php

require_once __DIR__ . '/PHPMailer-master/PHPMailerAutoload.php';

function createConfiguredMailer(): PHPMailer|false
{
    $username = trim((string)getenv('SMTP_USERNAME'));
    $password = (string)getenv('SMTP_PASSWORD');
    if ($username === '' || $password === '') {
        return false;
    }

    $mailer = new PHPMailer();
    $mailer->isSMTP();
    $mailer->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = $username;
    $mailer->Password = $password;
    $mailer->SMTPSecure = getenv('SMTP_SECURE') ?: 'tls';
    $mailer->Port = (int)(getenv('SMTP_PORT') ?: 587);
    $mailer->CharSet = 'UTF-8';
    $mailer->setFrom(getenv('SMTP_FROM_EMAIL') ?: $username, getenv('SMTP_FROM_NAME') ?: 'TechShyam');

    return $mailer;
}

function sendMail(string $replyTo, string $message, string $subject): bool
{
    $mailer = createConfiguredMailer();
    $recipient = trim((string)getenv('CONTACT_EMAIL')) ?: trim((string)getenv('SMTP_USERNAME'));
    if (!$mailer || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $mailer->addReplyTo($replyTo);
    }
    $mailer->addAddress($recipient);
    $mailer->isHTML(true);
    $mailer->Subject = $subject;
    $mailer->Body = $message;
    $mailer->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message)));

    return $mailer->send();
}

function responseMail(string $to, string $name): bool
{
    $mailer = createConfiguredMailer();
    if (!$mailer || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $mailer->addAddress($to, $name);
    $mailer->isHTML(true);
    $mailer->Subject = 'Thanks for contacting TechShyam';
    $mailer->Body = 'Hi ' . $safeName . ',<br><br>Thanks for getting in touch. I will respond as soon as possible.';
    $mailer->AltBody = 'Hi ' . $name . ', thanks for getting in touch. I will respond as soon as possible.';

    return $mailer->send();
}

function sendTestMail(string $to): bool
{
    $mailer = createConfiguredMailer();
    if (!$mailer || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $mailer->addAddress($to);
    $mailer->isHTML(false);
    $mailer->Subject = 'TechShyam SMTP test';
    $mailer->Body = 'SMTP settings are working. Sent at ' . date('Y-m-d H:i:s');

    return $mailer->send();
}

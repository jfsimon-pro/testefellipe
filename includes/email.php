<?php
// Envio de e-mail com PHPMailer via SMTP Gmail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jfsimon.pro@gmail.com'; // Troque pelo seu e-mail
        $mail->Password = 'agxrsgihmiletczt'; // Troque pela senha de app do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('jfsimon.pro@gmail.com', 'Plataforma Educacional');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
        return false;
    }
} 
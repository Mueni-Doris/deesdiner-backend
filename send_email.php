<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // adjust path if needed

/**
 * Sends reservation confirmation email.
 *
 * @param string $user_email
 * @param string $user_full_name
 * @param string $restaurant_name
 * @param string $reservation_date
 * @param string $reservation_time
 * @param int $number_of_guests
 * @return bool
 */
function sendReservationEmail(
    string $user_email,
    string $user_full_name,
    string $restaurant_name,
    string $reservation_date,
    string $reservation_time,
    int $number_of_guests
): bool {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // e.g., smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muenidoris22@gmail.com';
        $mail->Password   = 'gqrb nkxa dueh qdjb'; // use app password, not Gmail login
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('muenidoris22@gmail.com', 'Dees Diner');
        $mail->addAddress($user_email, $user_full_name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Your Reservation at {$restaurant_name}";
        $mail->Body    = "
            <p>Dear {$user_full_name},</p>
            <p>Thank you for your reservation at <b>{$restaurant_name}</b>. Here are your reservation details:</p>
            <p><b>Date:</b> {$reservation_date}</p>
            <p><b>Time:</b> {$reservation_time}</p>
            <p><b>Guests:</b> {$number_of_guests}</p>
            <p>Thank you for choosing Dees Diner!, see you soon</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

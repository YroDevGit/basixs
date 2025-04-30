<?php
require "_backend/core/mail/PHPMailer/src/PHPMailer.php";
require "_backend/core/mail/PHPMailer/src/SMTP.php";
require "_backend/core/mail/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {

    public static function email_template(string $email_template, array $content = []) {
        $template = "_frontend/pages/" . $email_template . ".php";
        if (file_exists($template)) {
            if (!empty($content)) {
                extract($content);
            }
            ob_start();
            include $template;
            $message = ob_get_clean();
            return $message;
        } else {
            return false;
        }
    }

    public static function send_email(string $to, string $subject, &$message, string $sender = "", string $sender_email = "") {
        if (!function_exists('has_internet_connection') || !has_internet_connection()) {
            return [
                "code" => getenv("no_internet_code"),
                "status" => "No Internet Connection",
                "message" => "Email not sent, No internet connection"
            ];
        }

        if (!$message) {
            return [
                "code" => getenv("notfound_code"),
                "status" => "Message Not Found",
                "message" => "Email Message Not Found.!"
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = getenv("smtp_host");
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv("smtp_user");
            $mail->Password   = getenv("smtp_password");
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv("smtp_port");

            $e_sender = $sender == "" ? getenv("sender_name") : $sender;
            $e_sendemail = $sender_email == "" ? getenv("sender_email") : $sender_email;

            $mail->setFrom($e_sendemail, $e_sender);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return [
                "code" => getenv("success_code"),
                "status" => "success",
                "message" => "Email sent successfully",
                "sender" => $e_sender,
                "receiver" => $to,
                "sender_email" => $e_sendemail
            ];
        } catch (Exception $e) {
            return [
                "code" => getenv("error_code"),
                "status" => "error",
                "message" => $mail->ErrorInfo
            ];
        }
    }
}
?>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


/* ===============================
   1. reCAPTCHA v3 Validation
================================ */

$secretKey = "6LdjGoosAAAAACneGCaDDk6h7fpKY3TPaahl1A1F";

if (!isset($_POST['recaptcha_token']) || empty($_POST['recaptcha_token'])) {
    echo "reCAPTCHA token missing";
    exit;
}

$token = $_POST['recaptcha_token'];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret' => $secretKey,
    'response' => $token
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response);

if (!$result->success || $result->score < 0.5) {
    echo "reCAPTCHA verification failed";
    exit;
}


/* ===============================
   2. Form Validation
================================ */

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$message    = trim($_POST['message'] ?? '');

if ($first_name === '' || !preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
    echo "Enter valid First Name";
    exit;
}

if ($last_name === '' || !preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
    echo "Enter valid Last Name";
    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Enter valid Email";
    exit;
}

if ($phone === '' || !preg_match("/^[0-9+\-\s]+$/", $phone)) {
    echo "Enter valid Phone number";
    exit;
}

if ($message === '') {
    echo "Message is required";
    exit;
}

$full_name = $first_name . " " . $last_name;


/* ===============================
   3. Send Email (PHPMailer)
================================ */

try {

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = "smtp.gmail.com";
$mail->SMTPAuth   = true;
$mail->Username   = "netcomenquiry@gmail.com";
$mail->Password   = "lorxmjvbuvbepuqp"; // Gmail App Password
$mail->SMTPSecure = "tls";
$mail->Port = 587;

$mail->setFrom("sunvolt24@gmail.com", "Website Contact Form");
$mail->addAddress("sunvolt24@gmail.com");

$mail->isHTML(true);
$mail->Subject = "New Contact Form Enquiry";

$mail->Body = "
<table border='1' cellpadding='10' width='100%'>
<tr>
<td><b>Name</b></td>
<td>".htmlspecialchars($full_name)."</td>
</tr>

<tr>
<td><b>Email</b></td>
<td>".htmlspecialchars($email)."</td>
</tr>

<tr>
<td><b>Phone</b></td>
<td>".htmlspecialchars($phone)."</td>
</tr>

<tr>
<td><b>Message</b></td>
<td>".nl2br(htmlspecialchars($message))."</td>
</tr>
</table>
";

$mail->send();


/* ===============================
   4. Auto Reply Mail
================================ */

$mail->clearAddresses();

$mail->addAddress($email);

$mail->Subject = "Thank you for contacting us";

$mail->Body = "
Dear $full_name,<br><br>

Thank you for contacting <b>Sunvolt Enterprises</b>.<br>
We received your enquiry and our team will contact you shortly.<br><br>

Regards,<br>
Team Sunvolt Enterprises
";

$mail->send();

echo "sent";

} 
catch (Exception $e) {
echo $mail->ErrorInfo;
}
?>
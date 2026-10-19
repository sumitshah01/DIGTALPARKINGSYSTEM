<?php
// api/contact.php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required = ['name', 'email', 'message'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Sanitize inputs
$name = htmlspecialchars(trim($_POST['name']));
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($_POST['message']));

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Load environment variables
$smptUser = $env('SMTP_USER', 'sorryshah7@gmail.com');
$smptPass = $env('SMTP_PASS', '');
$smptHost = $env('SMTP_HOST', 'smtp.gmail.com');
$smptPort = (int)$env('SMTP_PORT', 587);

// Send email using PHP mail() function
$to = $smptUser;
$subject = 'New Contact Form Message from SmartPark';
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

// Build email body
$body = "<h3>New Contact Form Submission</h3>";
$body .= "<p><strong>Name:</strong> $name</p>";
$body .= "<p><strong>Email:</strong> $email</p>";
$body .= "<p><strong>Message:</strong></p>";
$body .= "<p>$message</p>";

// Send the email
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}
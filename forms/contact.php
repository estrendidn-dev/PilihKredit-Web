<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Prepare the email
    $to = "godwish_jakin@hotmail.com";  // Replace with your email address
    $emailSubject = htmlspecialchars($subject); // Rename variable to avoid confusion
    $body = "You have received a new message from the contact form on your website.\n\n" .
        "Name: $name\n" .
        "Email: $email\n\n" .
        "Message:\n$message";

    $headers = "From: $email\r\n" .
        "Reply-To: $email\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n" .
        "X-Mailer: PHP/" . phpversion();

    // Send the email
    if (mail($to, $emailSubject, $body, $headers)) {
        echo "Message sent successfully!";
    } else {
        // Debugging: Print last error
        echo "Failed to send the message. Please try again later.";
        error_log(print_r(error_get_last(), true));
    }
} else {
    echo "Invalid request method.";
}

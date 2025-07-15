<?php
// Contact Form Email Handler for GNOD Technologies
// Set error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$to_email = "info@gnod-tech.co.za"; // Change this to your email
$from_email = "noreply@gnod-tech.co.za"; // Change this to your domain
$from_name = "GNOD Technologies Contact Form";

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data and sanitize
    $name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'])) : '';
    $discussion = isset($_POST['discussion']) ? trim(htmlspecialchars($_POST['discussion'])) : '';
    $subject = isset($_POST['subject']) ? trim(htmlspecialchars($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'])) : '';
    
    // Validation
    $errors = array();
    
    // Name validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long";
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Phone validation (optional but if provided, should be valid)
    if (!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
        $errors[] = "Please enter a valid phone number";
    }
    
    // Discussion topic validation
    if (empty($discussion)) {
        $errors[] = "Please select a discussion topic";
    }
    
    // Subject validation
    if (empty($subject)) {
        $errors[] = "Subject is required";
    } elseif (strlen($subject) < 5) {
        $errors[] = "Subject must be at least 5 characters long";
    }
    
    // Message validation
    if (empty($message)) {
        $errors[] = "Message is required";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long";
    }
    
    // If no validation errors, proceed with sending email
    if (empty($errors)) {
        
        // Prepare email content
        $email_subject = "New Contact Form Submission: " . $subject;
        
        // Create discussion topic mapping
        $discussion_topics = array(
            'web-development' => 'Web Development',
            'mobile-apps' => 'Mobile Applications',
            'custom-software' => 'Custom Software',
            'consultation' => 'IT Consultation',
            'project-quote' => 'Project Quote',
            'support' => 'Technical Support',
            'other' => 'Other'
        );
        
        $discussion_display = isset($discussion_topics[$discussion]) ? $discussion_topics[$discussion] : $discussion;
        
        // Email body
        $email_body = "
        <html>
        <head>
            <title>New Contact Form Submission</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                    New Contact Form Submission - GNOD Technologies
                </h2>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #2c3e50; margin-top: 0;'>Contact Information</h3>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                    " . (!empty($phone) ? "<p><strong>Phone:</strong> <a href='tel:{$phone}'>{$phone}</a></p>" : "") . "
                    <p><strong>Discussion Topic:</strong> {$discussion_display}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                </div>
                
                <div style='background-color: #e8f4fd; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #2c3e50; margin-top: 0;'>Message</h3>
                    <p style='white-space: pre-wrap;'>{$message}</p>
                </div>
                
                <div style='background-color: #f1f2f6; padding: 15px; border-radius: 5px; margin: 20px 0; font-size: 12px; color: #666;'>
                    <p><strong>Submission Details:</strong></p>
                    <p>Date: " . date('F j, Y \a\t g:i A') . "</p>
                    <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . "</p>
                    <p>User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "</p>
                </div>
                
                <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                <p style='text-align: center; color: #666; font-size: 12px;'>
                    This email was sent from the GNOD Technologies contact form.<br>
                    Please respond directly to the sender's email address.
                </p>
            </div>
        </body>
        </html>
        ";
        
        // Email headers
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: {$from_name} <{$from_email}>";
        $headers[] = "Reply-To: {$name} <{$email}>";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "X-Priority: 1";
        $headers[] = "X-MSMail-Priority: High";
        
        // Send email
        $mail_sent = mail($to_email, $email_subject, $email_body, implode("\r\n", $headers));
        
        if ($mail_sent) {
            $response['success'] = true;
            $response['message'] = "Thank you for your message! We have received your inquiry and will get back to you soon.";
            
            // Optional: Send confirmation email to user
            $user_subject = "Thank you for contacting GNOD Technologies";
            $user_body = "
            <html>
            <head>
                <title>Thank you for contacting us</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2c3e50;'>Thank you for contacting GNOD Technologies!</h2>
                    
                    <p>Dear {$name},</p>
                    
                    <p>We have received your message and appreciate you taking the time to reach out to us. Our team will review your inquiry and get back to you within 24-48 hours.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4 style='margin-top: 0;'>Your Message Summary:</h4>
                        <p><strong>Subject:</strong> {$subject}</p>
                        <p><strong>Topic:</strong> {$discussion_display}</p>
                        <p><strong>Submitted:</strong> " . date('F j, Y \a\t g:i A') . "</p>
                    </div>
                    
                    <p>In the meantime, if you have any urgent questions, please don't hesitate to call us at <strong>+27 79160 7483</strong>.</p>
                    
                    <p>Best regards,<br>
                    The GNOD Technologies Team</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='text-align: center; color: #666; font-size: 12px;'>
                        GNOD Technologies<br>
                        59 Blinkblaar, Zwartkop, Centurion, 0157, South Africa<br>
                        Phone: +27 79160 7483 | Email: info@gnod-tech.co.za
                    </p>
                </div>
            </body>
            </html>
            ";
            
            $user_headers = array();
            $user_headers[] = "MIME-Version: 1.0";
            $user_headers[] = "Content-Type: text/html; charset=UTF-8";
            $user_headers[] = "From: {$from_name} <{$from_email}>";
            $user_headers[] = "X-Mailer: PHP/" . phpversion();
            
            mail($email, $user_subject, $user_body, implode("\r\n", $user_headers));
            
        } else {
            $response['message'] = "Sorry, there was an error sending your message. Please try again later or contact us directly.";
        }
        
    } else {
        $response['message'] = "Please correct the following errors: " . implode(", ", $errors);
    }
    
} else {
    $response['message'] = "Invalid request method.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 
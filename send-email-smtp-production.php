<?php
/**
 * Production SMTP Email Handler for GNOD Technologies
 * This script uses PHPMailer with SMTP for reliable email delivery
 */

// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

// Load configuration
$config_file = 'email-config.php';
if (!file_exists($config_file)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Email configuration not found']));
}

$config = include $config_file;

// Load PHPMailer
$phpmailer_loaded = false;

// Try Composer autoload first
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $phpmailer_loaded = true;
    }
}

// Fallback to manual include
if (!$phpmailer_loaded && file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $phpmailer_loaded = true;
    }
}

if (!$phpmailer_loaded) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'PHPMailer library not found']));
}

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Rate limiting (prevent spam)
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_limit_file = 'rate_limit_' . md5($ip) . '.txt';
    $rate_limit_time = 300; // 5 minutes
    $max_requests = 3; // Max 3 requests per 5 minutes
    
    if (file_exists($rate_limit_file)) {
        $rate_data = json_decode(file_get_contents($rate_limit_file), true);
        if ($rate_data && (time() - $rate_data['time']) < $rate_limit_time) {
            if ($rate_data['count'] >= $max_requests) {
                http_response_code(429);
                die(json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']));
            }
            $rate_data['count']++;
        } else {
            $rate_data = ['time' => time(), 'count' => 1];
        }
    } else {
        $rate_data = ['time' => time(), 'count' => 1];
    }
    
    file_put_contents($rate_limit_file, json_encode($rate_data));
    
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
    } elseif (strlen($name) > 100) {
        $errors[] = "Name is too long";
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } elseif (strlen($email) > 254) {
        $errors[] = "Email is too long";
    }
    
    // Phone validation (optional but if provided, should be valid)
    if (!empty($phone)) {
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,20}$/', $phone)) {
            $errors[] = "Please enter a valid phone number";
        }
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
    } elseif (strlen($subject) > 200) {
        $errors[] = "Subject is too long";
    }
    
    // Message validation
    if (empty($message)) {
        $errors[] = "Message is required";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long";
    } elseif (strlen($message) > 5000) {
        $errors[] = "Message is too long";
    }
    
    // Spam detection
    $spam_indicators = ['viagra', 'casino', 'loan', 'credit', 'debt', 'free money', 'make money fast'];
    $message_lower = strtolower($message);
    foreach ($spam_indicators as $indicator) {
        if (strpos($message_lower, $indicator) !== false) {
            $errors[] = "Message contains inappropriate content";
            break;
        }
    }
    
    // If no validation errors, proceed with sending email
    if (empty($errors)) {
        
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
        
        // Email content
        $email_subject = "New Contact Form Submission: " . $subject;
        
        // Email body
        $email_body = createEmailBody($name, $email, $phone, $discussion_display, $subject, $message);
        
        // Send email using SMTP
        $mail_sent = sendEmailWithSMTP($config, $email_subject, $email_body, $name, $email);
        
        if ($mail_sent) {
            $response['success'] = true;
            $response['message'] = "Thank you for your message! We have received your inquiry and will get back to you within 24-48 hours.";
            
            // Send confirmation email to user
            $user_subject = "Thank you for contacting GNOD Technologies";
            $user_body = createConfirmationEmail($name, $subject, $discussion_display);
            
            sendEmailWithSMTP($config, $user_subject, $user_body, $config['from']['name'], $email);
            
            // Log successful submission
            logSubmission($name, $email, $subject, $discussion, true);
            
        } else {
            $response['message'] = "Sorry, there was an error sending your message. Please try again later or contact us directly at +27 79160 7483.";
            logSubmission($name, $email, $subject, $discussion, false);
        }
        
    } else {
        $response['message'] = "Please correct the following errors: " . implode(", ", $errors);
    }
    
} else {
    http_response_code(405);
    $response['message'] = "Invalid request method.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Send email using PHPMailer with SMTP
 */
function sendEmailWithSMTP($config, $subject, $body, $reply_name, $reply_email) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        
        // Timeout settings
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = false;
        
        // Recipients
        $mail->setFrom($config['from']['email'], $config['from']['name']);
        $mail->addAddress($config['to']['email']);
        $mail->addReplyTo($reply_email, $reply_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        // Additional headers for better deliverability
        $mail->addCustomHeader('X-Mailer', 'GNOD Technologies Contact Form');
        $mail->addCustomHeader('X-Priority', '1');
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create the main email body
 */
function createEmailBody($name, $email, $phone, $discussion_display, $subject, $message) {
    return "
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
}

/**
 * Create confirmation email body
 */
function createConfirmationEmail($name, $subject, $discussion_display) {
    return "
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
}

/**
 * Log form submissions for monitoring
 */
function logSubmission($name, $email, $subject, $discussion, $success) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'discussion' => $discussion,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'success' => $success
    ];
    
    $log_file = 'contact_form_log.json';
    $logs = [];
    
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    // Keep only last 1000 entries
    if (count($logs) >= 1000) {
        $logs = array_slice($logs, -999);
    }
    
    $logs[] = $log_entry;
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}
?> 
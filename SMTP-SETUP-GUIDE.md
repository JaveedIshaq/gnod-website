# SMTP Email Setup Guide for GNOD Technologies

## Why Use SMTP Instead of PHP mail()?

**PHP mail() function issues:**

- ❌ Often goes to spam folders
- ❌ Poor deliverability rates
- ❌ No delivery confirmation
- ❌ Limited error handling
- ❌ Server configuration dependent

**SMTP advantages:**

- ✅ High deliverability rates
- ✅ Professional email delivery
- ✅ Delivery tracking
- ✅ Better error handling
- ✅ Works with any email provider

## Installation Options

### Option 1: Using Composer (Recommended)

1. **Install Composer** (if not already installed):

   ```bash
   # Download Composer installer
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php -r "unlink('composer-setup.php');"
   mv composer.phar /usr/local/bin/composer
   ```

2. **Initialize Composer in your project**:

   ```bash
   cd /path/to/your/website
   composer init
   # Follow the prompts (you can press enter for defaults)
   ```

3. **Install PHPMailer**:

   ```bash
   composer require phpmailer/phpmailer
   ```

4. **Update your email script**:
   ```php
   // Add this at the top of send-email-smtp.php
   require 'vendor/autoload.php';
   ```

### Option 2: Manual Installation

1. **Download PHPMailer**:

   ```bash
   wget https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
   unzip master.zip
   mv PHPMailer-master/src PHPMailer
   rm -rf PHPMailer-master master.zip
   ```

2. **Update your email script**:
   ```php
   // Add these lines at the top of send-email-smtp.php
   require 'PHPMailer/Exception.php';
   require 'PHPMailer/PHPMailer.php';
   require 'PHPMailer/SMTP.php';
   ```

## Email Provider Configurations

### 1. Gmail SMTP Setup

**Configuration:**

```php
$smtp_config = array(
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password', // NOT your regular password!
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

**Gmail App Password Setup:**

1. Go to your Google Account settings
2. Enable 2-Factor Authentication
3. Go to Security → App passwords
4. Generate a new app password for "Mail"
5. Use this 16-character password in your config

### 2. Outlook/Hotmail SMTP Setup

**Configuration:**

```php
$smtp_config = array(
    'host' => 'smtp-mail.outlook.com',
    'port' => 587,
    'username' => 'your-email@outlook.com',
    'password' => 'your-password',
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

### 3. Yahoo Mail SMTP Setup

**Configuration:**

```php
$smtp_config = array(
    'host' => 'smtp.mail.yahoo.com',
    'port' => 587,
    'username' => 'your-email@yahoo.com',
    'password' => 'your-app-password',
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

### 4. Custom Domain Email (cPanel)

**Configuration:**

```php
$smtp_config = array(
    'host' => 'mail.yourdomain.com', // or smtp.yourdomain.com
    'port' => 587,
    'username' => 'noreply@yourdomain.com',
    'password' => 'your-email-password',
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

### 5. Professional Email Services

#### SendGrid

```php
$smtp_config = array(
    'host' => 'smtp.sendgrid.net',
    'port' => 587,
    'username' => 'apikey',
    'password' => 'your-sendgrid-api-key',
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

#### Mailgun

```php
$smtp_config = array(
    'host' => 'smtp.mailgun.org',
    'port' => 587,
    'username' => 'postmaster@yourdomain.mailgun.org',
    'password' => 'your-mailgun-password',
    'encryption' => 'tls',
    'from_email' => 'noreply@gnod-tech.co.za',
    'from_name' => 'GNOD Technologies Contact Form',
    'to_email' => 'info@gnod-tech.co.za'
);
```

## Implementation Steps

### Step 1: Choose Your Email Provider

Select one of the configurations above based on your email provider.

### Step 2: Update Configuration

Edit `send-email-smtp.php` and update the `$smtp_config` array with your details.

### Step 3: Update Form Action

Change your contact form to use the new script:

```html
<form action="send-email-smtp.php" method="POST"></form>
```

### Step 4: Test the Setup

1. Fill out your contact form
2. Check if you receive the email
3. Check spam folder if not received
4. Check error logs for any issues

## Troubleshooting

### Common Issues:

1. **"SMTP connect() failed"**

   - Check if port 587 is open on your server
   - Try port 465 with SSL encryption
   - Verify SMTP credentials

2. **"Authentication failed"**

   - Double-check username and password
   - For Gmail, ensure you're using an App Password
   - Enable "Less secure app access" (not recommended)

3. **"Connection timeout"**

   - Check firewall settings
   - Try different ports (25, 465, 587)
   - Contact your hosting provider

4. **Emails going to spam**
   - Set up SPF, DKIM, and DMARC records
   - Use a professional email service
   - Avoid spam trigger words

### Debug Mode:

Uncomment this line in the script to see detailed SMTP communication:

```php
$mail->SMTPDebug = 2;
```

## Security Best Practices

1. **Never commit credentials to version control**
2. **Use environment variables for sensitive data**
3. **Enable SSL/TLS encryption**
4. **Use App Passwords instead of regular passwords**
5. **Regularly rotate passwords**
6. **Monitor email delivery logs**

## Production Checklist

- [ ] Install PHPMailer via Composer
- [ ] Configure SMTP settings
- [ ] Test email delivery
- [ ] Remove debug mode
- [ ] Set up email monitoring
- [ ] Configure SPF/DKIM records
- [ ] Test spam score
- [ ] Set up backup email service

## Alternative: Environment Variables

For better security, use environment variables:

```php
$smtp_config = array(
    'host' => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
    'port' => $_ENV['SMTP_PORT'] ?? 587,
    'username' => $_ENV['SMTP_USERNAME'] ?? '',
    'password' => $_ENV['SMTP_PASSWORD'] ?? '',
    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@gnod-tech.co.za',
    'from_name' => $_ENV['FROM_NAME'] ?? 'GNOD Technologies Contact Form',
    'to_email' => $_ENV['TO_EMAIL'] ?? 'info@gnod-tech.co.za'
);
```

Create a `.env` file:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_ENCRYPTION=tls
FROM_EMAIL=noreply@gnod-tech.co.za
FROM_NAME=GNOD Technologies Contact Form
TO_EMAIL=info@gnod-tech.co.za
```

This setup will give you professional, reliable email delivery for your contact form!

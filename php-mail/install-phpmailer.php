<?php
/**
 * PHPMailer Installation Script for GNOD Technologies
 * This script helps you install PHPMailer for SMTP email functionality
 */

echo "=== PHPMailer Installation Script ===\n\n";

// Check if Composer is available
echo "1. Checking for Composer...\n";
$composer_available = false;

// Method 1: Check if composer.phar exists
if (file_exists('composer.phar')) {
    echo "   ✓ Found composer.phar in current directory\n";
    $composer_available = true;
    $composer_command = 'php composer.phar';
}

// Method 2: Check if composer is in PATH
if (!$composer_available) {
    $output = shell_exec('which composer 2>/dev/null');
    if ($output) {
        echo "   ✓ Found Composer in system PATH\n";
        $composer_available = true;
        $composer_command = 'composer';
    }
}

if (!$composer_available) {
    echo "   ✗ Composer not found\n";
    echo "   Installing Composer...\n";
    
    // Download Composer installer
    $installer_url = 'https://getcomposer.org/installer';
    $installer_file = 'composer-setup.php';
    
    if (file_put_contents($installer_file, file_get_contents($installer_url))) {
        echo "   ✓ Downloaded Composer installer\n";
        
        // Run installer
        $output = shell_exec("php $installer_file 2>&1");
        echo "   Installer output: $output\n";
        
        // Clean up
        unlink($installer_file);
        
        if (file_exists('composer.phar')) {
            echo "   ✓ Composer installed successfully\n";
            $composer_available = true;
            $composer_command = 'php composer.phar';
        } else {
            echo "   ✗ Composer installation failed\n";
        }
    } else {
        echo "   ✗ Failed to download Composer installer\n";
    }
}

echo "\n2. Setting up project...\n";

// Check if composer.json exists
if (!file_exists('composer.json')) {
    echo "   Creating composer.json...\n";
    
    $composer_json = [
        'name' => 'gnod-tech/website',
        'description' => 'GNOD Technologies Website',
        'type' => 'project',
        'require' => [
            'phpmailer/phpmailer' => '^6.8'
        ],
        'autoload' => [
            'psr-4' => [
                'App\\' => 'src/'
            ]
        ]
    ];
    
    if (file_put_contents('composer.json', json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        echo "   ✓ composer.json created\n";
    } else {
        echo "   ✗ Failed to create composer.json\n";
        exit(1);
    }
} else {
    echo "   ✓ composer.json already exists\n";
}

echo "\n3. Installing PHPMailer...\n";

if ($composer_available) {
    $output = shell_exec("$composer_command install 2>&1");
    echo "   Composer output:\n$output\n";
    
    if (file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        echo "   ✓ PHPMailer installed successfully\n";
    } else {
        echo "   ✗ PHPMailer installation failed\n";
        echo "   Trying manual installation...\n";
        
        // Manual installation fallback
        if (manualInstallPHPMailer()) {
            echo "   ✓ PHPMailer installed manually\n";
        } else {
            echo "   ✗ Manual installation also failed\n";
            exit(1);
        }
    }
} else {
    echo "   ✗ Composer not available, trying manual installation...\n";
    if (manualInstallPHPMailer()) {
        echo "   ✓ PHPMailer installed manually\n";
    } else {
        echo "   ✗ Manual installation failed\n";
        exit(1);
    }
}

echo "\n4. Creating configuration file...\n";

$config_template = '<?php
// SMTP Configuration for GNOD Technologies
// Update these values with your email provider settings

return [
    "smtp" => [
        "host" => "smtp.gmail.com", // Change to your SMTP server
        "port" => 587, // Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted)
        "username" => "your-email@gmail.com", // Your email
        "password" => "your-app-password", // Your email password or app password
        "encryption" => "tls", // "tls", "ssl", or "" for no encryption
    ],
    "from" => [
        "email" => "noreply@gnod-tech.co.za", // Your domain email
        "name" => "GNOD Technologies Contact Form"
    ],
    "to" => [
        "email" => "info@gnod-tech.co.za" // Where to receive contact form emails
    ]
];
';

if (file_put_contents('email-config.php', $config_template)) {
    echo "   ✓ Configuration file created (email-config.php)\n";
    echo "   ⚠️  Please update email-config.php with your actual email settings\n";
} else {
    echo "   ✗ Failed to create configuration file\n";
}

echo "\n5. Testing setup...\n";

// Test if PHPMailer can be loaded
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "   ✓ PHPMailer loaded successfully via Composer\n";
    } else {
        echo "   ✗ PHPMailer class not found\n";
    }
} elseif (file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "   ✓ PHPMailer loaded successfully manually\n";
    } else {
        echo "   ✗ PHPMailer class not found\n";
    }
} else {
    echo "   ✗ PHPMailer files not found\n";
}

echo "\n=== Installation Complete ===\n\n";
echo "Next steps:\n";
echo "1. Update email-config.php with your email provider settings\n";
echo "2. Update your contact form to use send-email-smtp.php\n";
echo "3. Test the contact form\n";
echo "4. Check the SMTP-SETUP-GUIDE.md for detailed configuration options\n\n";

/**
 * Manual PHPMailer installation
 */
function manualInstallPHPMailer() {
    echo "   Downloading PHPMailer...\n";
    
    // Create PHPMailer directory
    if (!is_dir('PHPMailer')) {
        mkdir('PHPMailer', 0755, true);
    }
    
    // Download PHPMailer files
    $files = [
        'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
        'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
        'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
    ];
    
    foreach ($files as $filename => $url) {
        $content = file_get_contents($url);
        if ($content !== false) {
            if (file_put_contents("PHPMailer/$filename", $content)) {
                echo "   ✓ Downloaded $filename\n";
            } else {
                echo "   ✗ Failed to save $filename\n";
                return false;
            }
        } else {
            echo "   ✗ Failed to download $filename\n";
            return false;
        }
    }
    
    return true;
}
?> 
<?php
/**
 * Direct Email Test (CLI friendly)
 */

require_once 'config.php';
require_once 'email_util.php';

echo "=== Direct Email Send Test ===\n\n";

// Create email utility
$emailUtil = new EmailUtil();

// Test email
$test_email = 'mukk47@gmail.com';
$test_name = 'Test User';
$test_token = 'test_token_' . time();

echo "Sending test verification email...\n";
echo "Email: " . $test_email . "\n";
echo "Name: " . $test_name . "\n";
echo "Token: " . $test_token . "\n\n";

$result = $emailUtil->sendVerificationEmail($test_email, $test_name, $test_token);

echo "=== Result ===\n";
if ($result['success']) {
    echo "✓ SUCCESS: " . $result['message'] . "\n";
} else {
    echo "✗ FAILED: " . $result['error'] . "\n";
}

echo "\nCheck error log for detailed SMTP messages.\n";
echo "Error log location: " . ini_get('error_log') . "\n";
?>

<?php
/**
 * Email Utility for Send Email 
 * @developer Mukesh Kumar
 * @version 1.0.0
 * @since 2026-02-16
 * @package EmailUtil
 * @subpackage EmailUtil
 * @category EmailUtil
 * @subcategory EmailUtil
 * @copyright 2026 Mukesh Kumar
 * @license MIT
 * @link https://github.com/mkv1992/email_util  
 */

class EmailUtil {
    private $from_email;
    private $from_name;
    
    public function __construct($from_email = null, $from_name = null) {
        // Use configured sender details from config.php
        $this->from_email = $from_email ?? (defined('SENDER_EMAIL') ? SENDER_EMAIL : ($_SERVER['HTTP_HOST'] . '@' . $_SERVER['HTTP_HOST']));
        $this->from_name = 'Webetron';
    }
    
    /**
     * Send verification email"
     * @developer Mukesh Kumar
     * @version 1.0.0
     * @since 2026-02-16
     * @package EmailUtil
     * @subpackage EmailUtil
     * @category EmailUtil
     * @subcategory EmailUtil
     * @copyright 2026 Mukesh Kumar
     * @license MIT
     * @link https://github.com/mkv1992/email_util  
     * @param string $to_email
     * @param string $to_name
     * @param string $verification_token
     * @return array
    
     */
    public function sendVerificationEmail($to_email, $to_name, $verification_token) {
        try {
           
            $subject = 'Verify Your Email Address - ';
            
            $html_body = "Mukesh Kumar test Email";
            
            
            $result = $this->sendEmail($to_email, $to_name, $subject, $html_body);
            
            if ($result['success']) {
                error_log('[' . date('Y-m-d H:i:s') . '] ✓ Verification Email SENT to: ' . $to_email);
            } else {
                error_log('[' . date('Y-m-d H:i:s') . '] ✗ Verification Email FAILED to: ' . $to_email . ' | Error: ' . ($result['error'] ?? 'Unknown error'));
            }
            
            return $result;
            
        } catch (Exception $e) {
            $error_msg = 'Exception in sendVerificationEmail: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine();
            error_log('[' . date('Y-m-d H:i:s') . '] ' . $error_msg);
            return ['success' => false, 'error' => $error_msg];
        }
    }
    
  
        /**
         * Send generic email
         * @developer Mukesh Kumar
         * @version 1.0.0
         * @since 2026-02-16
         * @package EmailUtil
         * @subpackage EmailUtil
         * @category EmailUtil
         * @subcategory EmailUtil
         * @copyright 2026 Mukesh Kumar
         * @license MIT
         */
        private function sendEmail($to_email, $to_name, $subject, $html_body) {
            // Validate email
            if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Invalid email address'];
            }
            
                return $this->sendViaSMTP($to_email, $to_name, $subject, $html_body);
           
        }
        
    /**
     * Send email via SMTP
     * Supports both SSL (port 465) and TLS (port 587)
     * Fixed  and other SMTP services
     * @developer Mukesh Kumar
     * @version 1.0.0
     * @since 2026-02-16
     * @package EmailUtil
     * @subpackage EmailUtil
     * @category EmailUtil
     * @subcategory EmailUtil
     * @copyright 2026 Mukesh Kumar
     * @license MIT
     */
    private function sendViaSMTP($to_email, $to_name, $subject, $html_body) {
        $smtp_host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $smtp_port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $smtp_user = defined('SMTP_USER') ? SMTP_USER : '';
        $smtp_password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        
        // Auto-detect encryption based on port if not explicitly set
        $use_ssl = ($smtp_port == 465) ? true : (defined('SMTP_USE_SSL') ? SMTP_USE_SSL : false);
        $use_tls = ($smtp_port == 587) ? true : (defined('SMTP_USE_TLS') ? SMTP_USE_TLS : false);
        
        try {
            $encryption_type = $use_ssl ? 'SSL/465' : ($use_tls ? 'TLS/587' : 'PLAIN');
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Attempting to connect to ' . $smtp_host . ':' . $smtp_port . ' (Encryption: ' . $encryption_type . ')');
            
            $handle = null;
            
            // For SSL (port 465), use stream_socket_client with SSL context
            if ($smtp_port == 465) {
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Using SSL/465 connection');
                
                $context = stream_context_create([
                    'ssl' => [
                        'ciphers' => 'DEFAULT:!DH',
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]);
                
                $handle = @stream_socket_client(
                    'ssl://' . $smtp_host . ':' . $smtp_port,
                    $errno,
                    $errstr,
                    15,
                    STREAM_CLIENT_CONNECT,
                    $context
                );
            } else {
                // For TLS (port 587) or plain, use stream_socket_client
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Using plain connection for TLS upgrade or plain SMTP');
                
                $handle = @stream_socket_client(
                    'tcp://' . $smtp_host . ':' . $smtp_port,
                    $errno,
                    $errstr,
                    15
                );
            }
            
            if (!$handle) {
                $error = 'Connection failed: ' . $errstr . ' (' . $errno . ')';
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP Connection FAILED to ' . $smtp_host . ':' . $smtp_port . ' - ' . $error);
                return ['success' => false, 'error' => 'SMTP connection failed: ' . $error];
            }
            
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Socket connected, reading greeting...');
            
            // Set proper stream handling
            stream_set_blocking($handle, true);
            stream_set_timeout($handle, 15);
            
            // Read server greeting
            $response = @fgets($handle, 1024);
            if (empty($response)) {
                fclose($handle);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: No greeting received (empty response)');
                return ['success' => false, 'error' => 'SMTP server did not respond with greeting'];
            }
            
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Greeting: ' . trim($response));
            
            if (strpos($response, '220') === false) {
                fclose($handle);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Invalid greeting response: ' . trim($response));
                return ['success' => false, 'error' => 'SMTP server invalid response: ' . trim($response)];
            }
            
            // Send EHLO command
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Sending EHLO');
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (gethostname() ?: 'localhost');
            fputs($handle, "EHLO " . $host . "\r\n");
            $response = @fgets($handle, 1024);
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: EHLO response: ' . trim($response));
            
            // Read multiline EHLO response
            while (preg_match('/^\d{3}-/', $response)) {
                $response = @fgets($handle, 1024);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: EHLO continued: ' . trim($response));
            }
            
            // Upgrade to TLS if needed (port 587)
            if ($smtp_port == 587) {
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Sending STARTTLS');
                fputs($handle, "STARTTLS\r\n");
                $response = @fgets($handle, 1024);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: STARTTLS response: ' . trim($response));
                
                if (strpos($response, '220') !== false) {
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Enabling TLS encryption');
                    
                    if (!@stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                        error_log('[' . date('Y-m-d H:i:s') . '] SMTP: TLS upgrade FAILED');
                        fclose($handle);
                        return ['success' => false, 'error' => 'Failed to upgrade connection to TLS'];
                    }
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: TLS encryption enabled');
                    
                    // Send EHLO again after TLS
                    fputs($handle, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
                    $response = @fgets($handle, 1024);
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: EHLO after TLS: ' . trim($response));
                }
            }
            
            // Authenticate
            if (!empty($smtp_user) && !empty($smtp_password)) {
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Starting AUTH LOGIN');
                fputs($handle, "AUTH LOGIN\r\n");
                $response = @fgets($handle, 1024);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: AUTH response: ' . trim(substr($response, 0, 3)));
                
                if (strpos($response, '334') !== false) {
                    fputs($handle, base64_encode($smtp_user) . "\r\n");
                    $response = @fgets($handle, 1024);
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Username sent');
                    
                    fputs($handle, base64_encode($smtp_password) . "\r\n");
                    $response = @fgets($handle, 1024);
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Password sent - Response code: ' . substr(trim($response), 0, 3));
                    
                    if (strpos($response, '235') === false && strpos($response, '250') === false) {
                        fclose($handle);
                        error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Auth FAILED - ' . trim($response));
                        return ['success' => false, 'error' => 'SMTP authentication failed. Check API key credentials.'];
                    }
                    error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Authentication SUCCESSFUL');
                }
            }
            
            // Send MAIL FROM
            fputs($handle, "MAIL FROM: <" . $this->from_email . ">\r\n");
            $response = @fgets($handle, 1024);
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: MAIL FROM response: ' . substr(trim($response), 0, 3));
            
            // Send RCPT TO
            fputs($handle, "RCPT TO: <" . $to_email . ">\r\n");
            $response = @fgets($handle, 1024);
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: RCPT TO response: ' . substr(trim($response), 0, 3));
            
            if (strpos($response, '250') === false && strpos($response, '251') === false) {
                fclose($handle);
                error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Recipient rejected - ' . trim($response));
                return ['success' => false, 'error' => 'Recipient rejected by SMTP server: ' . trim($response)];
            }
            
            // Send DATA
            fputs($handle, "DATA\r\n");
            $response = @fgets($handle, 1024);
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: DATA response: ' . substr(trim($response), 0, 3));
            
            // Prepare email headers
            $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
            $headers .= "To: " . $to_name . " <" . $to_email . ">\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Reply-To: " . $this->from_email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "X-Priority: 3\r\n";
            
            $message = $headers . "\r\n" . $html_body;
            
            // Send message
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Sending message (' . strlen($message) . ' bytes)');
            fputs($handle, $message . "\r\n.\r\n");
            $response = @fgets($handle, 1024);
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP: Message response: ' . trim($response));
            
            // Close connection
            fputs($handle, "QUIT\r\n");
            @fgets($handle, 1024);
            fclose($handle);
            
            if (strpos($response, '250') !== false) {
                error_log('[' . date('Y-m-d H:i:s') . '] ✓ EMAIL SENT via SMTP to: ' . $to_email);
                return ['success' => true, 'message' => 'Email sent successfully via SMTP'];
            } else {
                error_log('[' . date('Y-m-d H:i:s') . '] ✗ SMTP rejected message: ' . trim($response));
                return ['success' => false, 'error' => 'SMTP server rejected message'];
            }
            
        } catch (Exception $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] SMTP Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'SMTP error: ' . $e->getMessage()];
        }
    }
    
   
   
}
?>

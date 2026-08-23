<?php

class EmailHelper {
    private $apiUrl = 'https://dev.thehappyhouse.au/EmailAPI.php'; 
    private $apiKey; 

    public function __construct($apiUrl = null) {
        if ($apiUrl) {
            $this->apiUrl = $apiUrl;
        }
        $this->apiKey = $this->getApiKey();
    }

    private function getApiKey() {
        $jsonFilePath = dirname(__DIR__) . '/API_KEYS.json';
        if (!file_exists($jsonFilePath)) {
            throw new Exception("API keys file not found.");
        }
        $jsonData = file_get_contents($jsonFilePath);
        $data = json_decode($jsonData, true);

        if (isset($data['mail_key'])) {
            return $data['mail_key']; 
        } else {
            throw new Exception("API key not found in the JSON file.");
        }
    }

    public function getEmailTemplate($templateFile, $variables = []) {
        if (!file_exists($templateFile)) {
            return false;
        }
        $templateContent = file_get_contents($templateFile);
        foreach ($variables as $key => $value) {
            $templateContent = str_replace("{{" . $key . "}}", $value, $templateContent);
        }
        return $templateContent;
    }

    /**
     * Modified sendEmail to include optional template_name
     * Existing calls with 3 parameters will still work perfectly.
     */
    public function sendEmail($recipient, $subject, $message, $template_name = 'general') {

        $data = [
            'recipient' => $recipient,
            'subject'   => $subject,
            'message'   => $message, 
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = 'cURL Error: ' . curl_error($ch);
            $this->logEmailStatus($recipient, $subject, $message, 'error', $error_msg);
            $this->dbLogEmailStatus($recipient, $subject, $template_name, 'failed', $error_msg);
            return [
                'status' => 'error',
                'message' => $error_msg,
            ];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        $status = isset($responseData['status']) ? $responseData['status'] : 'unknown';
        
        // Final Status for DB (converting API response to our ENUM)
        $dbStatus = ($status === 'success') ? 'sent' : 'failed';

        // 1. Existing File Log
        $this->logEmailStatus($recipient, $subject, $message, $status, $response);
        
        // 2. NEW Database Log
        $this->dbLogEmailStatus($recipient, $subject, $template_name, $dbStatus, $response);
        
        return [
            'status' => $status,
            'message' => $response,
        ];
    }

    // --- NEW METHOD: LOG TO DATABASE ---
    private function dbLogEmailStatus($recipient, $subject, $template, $status, $response) {
        try {
            // Check if Data class exists (your DB handler)
            if (class_exists('Data')) {
                $db = new Data();
                $safe_subject = addslashes($subject);
                $safe_response = addslashes($this->sanitizeMessage($response));
                
                $sql = "INSERT INTO email_logs (recipient_email, subject, template_name, status, error_message) 
                        VALUES ('$recipient', '$safe_subject', '$template', '$status', '$safe_response')";
                
                $db->execute($sql);
            }
        } catch (Exception $e) {
            // Fail silently to prevent email sending from crashing if DB logging fails
            file_put_contents('email_log.txt', "DB LOGGING FAILED: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    private function logEmailStatus($recipient, $subject, $message, $status, $response) {
        $logFilePath = 'email_log.txt'; 
        $logMessage = sprintf(
            "[%s] Recipient: %s | Subject: %s | Status: %s | Message: %s | Response: %s\n",
            date('Y-m-d H:i:s'),
            $recipient,
            $subject,
            $status,
            $this->sanitizeMessage($message),
            $this->sanitizeMessage($response) 
        );
        file_put_contents($logFilePath, $logMessage, FILE_APPEND);
    }

    private function sanitizeMessage($message) {
        return str_replace(["\n", "\r", "\t"], " ", $message);
    }
}
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Twilio_verify_service
{
    private $accountSid;
    private $authToken;
    private $serviceSid;
    private $baseUrl = 'https://verify.twilio.com/v2/Services/';

    public function __construct()
    {
        $this->accountSid = trim((string) ($_ENV['TWILIO_ACCOUNT_SID'] ?? ''));
        $this->authToken = trim((string) ($_ENV['TWILIO_AUTH_TOKEN'] ?? ''));
        $this->serviceSid = trim((string) ($_ENV['TWILIO_VERIFY_SERVICE_SID'] ?? ''));
    }

    public function isConfigured()
    {
        return $this->accountSid !== '' && $this->authToken !== '' && $this->serviceSid !== '';
    }

    public function sendCode($phone, $channel = 'whatsapp')
    {
        $channel = $this->normalizeChannel($channel);

        return $this->request('/Verifications', array(
            'To' => $phone,
            'Channel' => $channel,
        ));
    }

    public function checkCode($phone, $code)
    {
        $result = $this->request('/VerificationCheck', array(
            'To' => $phone,
            'Code' => $code,
        ));

        $result['approved'] = !empty($result['success'])
            && isset($result['response']['status'])
            && $result['response']['status'] === 'approved';

        return $result;
    }

    private function request($path, array $fields)
    {
        if (!$this->isConfigured()) {
            return array('success' => false, 'error' => 'not_configured', 'response' => array());
        }

        if (!function_exists('curl_init')) {
            log_message('error', 'Twilio Verify request failed because the PHP cURL extension is unavailable.');
            return array('success' => false, 'error' => 'curl_unavailable', 'response' => array());
        }

        $url = $this->baseUrl . rawurlencode($this->serviceSid) . $path;
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->accountSid . ':' . $this->authToken,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));

        $body = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        $response = is_string($body) ? json_decode($body, true) : array();
        $response = is_array($response) ? $response : array();
        $success = $curlError === '' && $statusCode >= 200 && $statusCode < 300;

        if (!$success) {
            $providerCode = isset($response['code']) ? (string) $response['code'] : 'transport_error';
            log_message('error', 'Twilio Verify request failed. HTTP ' . $statusCode . ', provider code ' . $providerCode . '.');
        }

        return array(
            'success' => $success,
            'error' => $success ? null : ($curlError !== '' ? 'transport_error' : 'provider_error'),
            'status_code' => $statusCode,
            'provider_code' => $response['code'] ?? null,
            'response' => $response,
        );
    }

    private function normalizeChannel($channel)
    {
        return strtolower((string) $channel) === 'sms' ? 'sms' : 'whatsapp';
    }
}

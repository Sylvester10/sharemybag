<?php
function send_infobip_otp($phone)
{
    $apiKey        = $_ENV['INFOBIP_API_KEY']  ?? '';
    $baseUrl       = $_ENV['INFOBIP_BASE_URL'] ?? '';
    $applicationId = $_ENV['INFOBIP_APP_ID']   ?? '';
    $messageId     = $_ENV['INFOBIP_MSG_ID']   ?? '';

    if (empty($applicationId) || empty($messageId)) {
        return ['status' => false, 'response' => 'Infobip helper is not configured with Application ID and Message ID.'];
    }

    $url = "$baseUrl/2fa/2/pin";

    $payload = json_encode([
        "applicationId" => $applicationId,
        "messageId"     => $messageId,
        "from"          => "ServiceSMS",
        "to"            => $phone
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: App $apiKey",
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT    => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status' => false, 'response' => 'cURL Error: ' . $error];
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return ['status' => true, 'pinId' => $result['pinId']];
    } else {
        return ['status' => false, 'response' => $response, 'httpCode' => $httpCode];
    }
}


function verify_infobip_otp($pinId, $pin)
{
    $apiKey  = $_ENV['INFOBIP_API_KEY']  ?? '';
    $baseUrl = $_ENV['INFOBIP_BASE_URL'] ?? '';
    $url     = "$baseUrl/2fa/2/pin/$pinId/verify";

    $payload = json_encode(["pin" => $pin]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: App $apiKey",
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT    => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status' => false, 'response' => 'cURL Error: ' . $error];
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['verified']) && $result['verified'] === true) {
            return ['status' => true, 'response' => $result];
        } else {
            return ['status' => false, 'response' => $result];
        }
    } else {
        return ['status' => false, 'response' => $response, 'httpCode' => $httpCode];
    }
}

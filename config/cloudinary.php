<?php
function uploadToCloudinary($tmpPath, $publicId) {
    $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
    $apiKey    = $_ENV['CLOUDINARY_API_KEY'];
    $apiSecret = $_ENV['CLOUDINARY_API_SECRET'];

    $timestamp = time();
    $signature = sha1("public_id=$publicId&timestamp=$timestamp" . $apiSecret);

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/upload");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file'      => new CURLFile($tmpPath),
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key'   => $apiKey,
        'signature' => $signature,
    ]);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $response['secure_url'] ?? null;
}
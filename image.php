<?php
// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Check if the URL parameter is set
if (isset($_GET['url'])) {
    $imageUrl = $_GET['url'];

    // Validate the URL or sanitize input here as needed
    $imageUrl = filter_var($imageUrl, FILTER_SANITIZE_URL);

    // Ensure the image URL is not empty
    if (!empty($imageUrl)) {
        // Use cURL for better HTTP/2 compatibility
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ImageProxy/1.0)');
        
        $imageContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($imageContent === false || $httpCode !== 200) {
            http_response_code(404);
            exit;
        }

        // Detect content type from URL if not provided
        if (empty($contentType) || strpos($contentType, 'image') === false) {
            $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml'
            ];
            $contentType = $mimeTypes[$extension] ?? 'image/jpeg';
        }

        // Set proper headers for HTTP/2 compatibility
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . strlen($imageContent));
        header('Cache-Control: public, max-age=86400');
        header('Connection: close');
        
        echo $imageContent;
        exit;
    }
}

// If URL is not valid, or image cannot be fetched, return 404
http_response_code(404);
header('Content-Type: text/plain');
echo 'Image not found';

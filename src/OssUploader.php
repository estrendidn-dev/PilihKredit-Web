<?php

namespace PilihKredit;

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * OSS Upload Utility Class
 */
class OssUploader
{
    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $bucket;
    private $bucketDomain;
    private $ossClient;

    /**
     * Constructor
     * 
     * @param string $accessKeyId OSS AccessKeyId
     * @param string $accessKeySecret OSS AccessKeySecret
     * @param string $endpoint OSS Endpoint (e.g.: oss-ap-southeast-5.aliyuncs.com, without protocol)
     * @param string $bucket OSS Bucket name
     * @param string $bucketDomain OSS Bucket domain (e.g.: https://bucket-name.oss-region.aliyuncs.com)
     */
    public function __construct($accessKeyId, $accessKeySecret, $endpoint, $bucket, $bucketDomain = null)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        // Remove protocol prefix if present (OSS SDK needs domain only)
        $this->endpoint = preg_replace('#^https?://#', '', $endpoint);
        $this->bucket = $bucket;
        $this->bucketDomain = $bucketDomain;

        try {
            $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $this->endpoint);
        } catch (OssException $e) {
            throw new \Exception("OSS client initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Upload JSON content to OSS
     * 
     * @param string $jsonContent JSON string content
     * @param string $objectName OSS object name (file path, e.g.: contact-forms/2025/01/contact-20250101-123456.json)
     * @param string $acl ACL setting (default: 'private', options: 'private', 'public-read', 'public-read-write')
     * @return string Returns the OSS file URL
     * @throws \Exception
     */
    public function uploadJson($jsonContent, $objectName, $acl = 'private')
    {
        try {
            // Set options for upload
            $options = [
                \OSS\OssClient::OSS_CONTENT_TYPE => 'application/json; charset=utf-8',
            ];
            
            // Set ACL if specified (valid values: private, public-read, public-read-write)
            if ($acl && in_array($acl, ['private', 'public-read', 'public-read-write'])) {
                $options[\OSS\OssClient::OSS_OBJECT_ACL] = $acl;
            }
            
            // Upload JSON content
            $result = $this->ossClient->putObject($this->bucket, $objectName, $jsonContent, $options);
            
            // Construct file URL
            // Use bucketDomain if provided, otherwise construct from bucket and endpoint
            if ($this->bucketDomain) {
                $url = rtrim($this->bucketDomain, '/') . '/' . $objectName;
            } else {
                $url = "https://" . $this->bucket . "." . $this->endpoint . "/" . $objectName;
            }
            
            return $url;
        } catch (OssException $e) {
            $errorMsg = $e->getMessage();
            $errorCode = $e->getErrorCode();
            
            // Provide more detailed error information
            $detailedError = "OSS upload failed";
            if ($errorCode) {
                $detailedError .= " [Code: {$errorCode}]";
            }
            $detailedError .= ": {$errorMsg}";
            
            // Add troubleshooting hints for common errors
            if (strpos($errorMsg, 'AccessDenied') !== false || strpos($errorMsg, 'bucket acl') !== false) {
                $detailedError .= " | Hint: Check if AccessKey has write permission for bucket '{$this->bucket}' and verify bucket ACL settings.";
            }
            
            throw new \Exception($detailedError);
        }
    }
    
    /**
     * Get signed URL for file (for private Bucket)
     * 
     * @param string $objectName OSS object name
     * @param int $timeout Signed URL validity period in seconds, default 3600 seconds (1 hour)
     * @return string Signed URL
     */
    public function getSignedUrl($objectName, $timeout = 3600)
    {
        try {
            return $this->ossClient->signUrl($this->bucket, $objectName, $timeout);
        } catch (OssException $e) {
            throw new \Exception("Failed to get signed URL: " . $e->getMessage());
        }
    }

    /**
     * Generate unique file name
     * 
     * @param string $prefix File name prefix
     * @return string Complete OSS object name
     */
    public function generateObjectName($prefix = 'contact-form')
    {
        $date = date('Y/m/d');
        $timestamp = date('YmdHis');
        $random = mt_rand(1000, 9999);
        
        return $prefix . '/' . $date . '/' . $prefix . '-' . $timestamp . '-' . $random . '.json';
    }
}


<?php
header('Content-Type: application/json');

// Load Composer autoloader
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Composer dependencies not installed. Please run "composer install" on the server.'
    ]);
    exit;
}
require_once $autoloadPath;

// Load OSS Uploader class
use PilihKredit\OssUploader;

// Load OSS configuration
$ossConfig = require __DIR__ . '/config/oss_config.php';

// Database connection
$servername = "rm-d9j40fh5e5ny3vr23zo.mysql.ap-southeast-5.rds.aliyuncs.com";
$username = "pk_credit_core_pro_user";
$password = "qie7rjuzn5nvs35t3znhdk7lamsgh0@";
$dbname = "pk_credit_admin";

$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$data = $_POST;

// Validate required fields
$requiredFields = [
    'nama', 'handphone', 'email', 'pekerjaan', 'pendapatan', 'jaminan',
    'tujuan_pinjaman', 'pinjaman_berjaminan', 'riwayat_berjaminan',
    'riwayat_non_berjaminan', 'total_kewajiban',
    'alamat_jaminan', 'luas_tanah', 'luas_bangunan', 'dokumen_jaminan', 'pemilik_sertifikat'
];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Validate pinjaman_non_berjaminan separately (it's an array)
// Note: When no checkbox is selected, the field won't exist in $_POST
if (!isset($data['pinjaman_non_berjaminan']) || !is_array($data['pinjaman_non_berjaminan']) || count($data['pinjaman_non_berjaminan']) === 0) {
    echo json_encode(['success' => false, 'message' => "Field 'pinjaman_non_berjaminan' is required. Silakan pilih setidaknya satu jenis pinjaman."]);
    exit;
}

// Sanitize and prepare data
$full_name = htmlspecialchars(trim($data['nama']));
$phone_number = htmlspecialchars(trim($data['handphone']));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$occupation = $data['pekerjaan'];
$monthly_income = (int) preg_replace('/[^0-9]/', '', $data['pendapatan']);
$collateral_type = $data['jaminan'];

$loan_purpose = $data['tujuan_pinjaman'];
$secured_loan_type = $data['pinjaman_berjaminan'];
$secured_payment_history = $data['riwayat_berjaminan'];
// Handle multiselect checkboxes - convert array to comma-separated string
$unsecured_loan_type = is_array($data['pinjaman_non_berjaminan']) 
    ? implode(',', $data['pinjaman_non_berjaminan']) 
    : $data['pinjaman_non_berjaminan'];
$unsecured_payment_history = $data['riwayat_non_berjaminan'];
$total_monthly_obligations = (int) preg_replace('/[^0-9]/', '', $data['total_kewajiban']);

$collateral_address = htmlspecialchars(trim($data['alamat_jaminan']));
$land_area = (float) $data['luas_tanah'];
$building_area = (float) $data['luas_bangunan'];
$collateral_document = $data['dokumen_jaminan'];
$certificate_owner = $data['pemilik_sertifikat'];

$is_residence = isset($data['tempat_tinggal']) ? $data['tempat_tinggal'] : 'tidak';
$is_business_place = isset($data['tempat_usaha']) ? $data['tempat_usaha'] : 'tidak';
$is_rented = isset($data['disewakan']) ? $data['disewakan'] : 'tidak';
$is_collateralized = isset($data['jaminan_kredit']) ? $data['jaminan_kredit'] : 'tidak';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $sql = "INSERT INTO pdaja_loan_applications (
        full_name, phone_number, email, occupation, monthly_income, collateral_type,
        loan_purpose, secured_loan_type, secured_payment_history,
        unsecured_loan_type, unsecured_payment_history, total_monthly_obligations,
        collateral_address, land_area, building_area, collateral_document, certificate_owner,
        is_residence, is_business_place, is_rented, is_collateralized
    ) VALUES (
        :full_name, :phone_number, :email, :occupation, :monthly_income, :collateral_type,
        :loan_purpose, :secured_loan_type, :secured_payment_history,
        :unsecured_loan_type, :unsecured_payment_history, :total_monthly_obligations,
        :collateral_address, :land_area, :building_area, :collateral_document, :certificate_owner,
        :is_residence, :is_business_place, :is_rented, :is_collateralized
    )";

    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':occupation', $occupation);
    $stmt->bindParam(':monthly_income', $monthly_income, PDO::PARAM_INT);
    $stmt->bindParam(':collateral_type', $collateral_type);
    
    $stmt->bindParam(':loan_purpose', $loan_purpose);
    $stmt->bindParam(':secured_loan_type', $secured_loan_type);
    $stmt->bindParam(':secured_payment_history', $secured_payment_history);
    $stmt->bindParam(':unsecured_loan_type', $unsecured_loan_type);
    $stmt->bindParam(':unsecured_payment_history', $unsecured_payment_history);
    $stmt->bindParam(':total_monthly_obligations', $total_monthly_obligations, PDO::PARAM_INT);
    
    $stmt->bindParam(':collateral_address', $collateral_address);
    $stmt->bindParam(':land_area', $land_area);
    $stmt->bindParam(':building_area', $building_area);
    $stmt->bindParam(':collateral_document', $collateral_document);
    $stmt->bindParam(':certificate_owner', $certificate_owner);
    
    $stmt->bindParam(':is_residence', $is_residence);
    $stmt->bindParam(':is_business_place', $is_business_place);
    $stmt->bindParam(':is_rented', $is_rented);
    $stmt->bindParam(':is_collateralized', $is_collateralized);

    $stmt->execute();
    
    $insertedId = $conn->lastInsertId();
    
    // Prepare data for OSS upload
    $ossData = [
        'application_id' => $insertedId,
        'submitted_at' => date('Y-m-d H:i:s'),
        'data_diri' => [
            'nama' => $full_name,
            'handphone' => $phone_number,
            'email' => $email,
            'pekerjaan' => $occupation,
            'pendapatan' => $monthly_income,
            'jaminan' => $collateral_type,
        ],
        'penilaian_mandiri' => [
            'tujuan_pinjaman' => $loan_purpose,
            'pinjaman_berjaminan' => $secured_loan_type,
            'riwayat_berjaminan' => $secured_payment_history,
            'pinjaman_non_berjaminan' => is_array($data['pinjaman_non_berjaminan']) 
                ? $data['pinjaman_non_berjaminan'] 
                : explode(',', $unsecured_loan_type),
            'riwayat_non_berjaminan' => $unsecured_payment_history,
            'total_kewajiban' => $total_monthly_obligations,
        ],
        'data_jaminan' => [
            'alamat_jaminan' => $collateral_address,
            'luas_tanah' => $land_area,
            'luas_bangunan' => $building_area,
            'dokumen_jaminan' => $collateral_document,
            'pemilik_sertifikat' => $certificate_owner,
            'tempat_tinggal' => $is_residence,
            'tempat_usaha' => $is_business_place,
            'disewakan' => $is_rented,
            'jaminan_kredit' => $is_collateralized,
        ],
    ];
    
    // Upload to OSS
    $ossUploadSuccess = false;
    $ossUrl = null;
    $ossError = null;
    
    // Log configuration being used (for debugging)
    error_log("OSS Upload attempt - Bucket: {$ossConfig['bucket']}, Endpoint: {$ossConfig['endpoint']}, AccessKeyId: " . substr($ossConfig['access_key_id'], 0, 10) . "...");
    
    try {
        $ossUploader = new OssUploader(
            $ossConfig['access_key_id'],
            $ossConfig['access_key_secret'],
            $ossConfig['endpoint'],
            $ossConfig['bucket'],
            $ossConfig['bucket_domain']
        );
        
        // Generate object name for loan application
        $date = date('Y/m/d');
        $timestamp = date('YmdHis');
        $random = mt_rand(1000, 9999);
        $objectName = 'loan-applications/' . $date . '/loan-' . $timestamp . '-' . $insertedId . '-' . $random . '.json';
        
        // Convert data to JSON
        $jsonContent = json_encode($ossData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Upload to OSS
        $ossUrl = $ossUploader->uploadJson($jsonContent, $objectName);
        $ossUploadSuccess = true;
        error_log("OSS upload successful for application ID {$insertedId}. URL: {$ossUrl}");
    } catch (Exception $e) {
        $ossError = $e->getMessage();
        // Log detailed error but don't fail the request since DB save was successful
        error_log("OSS upload failed for application ID {$insertedId}: " . $ossError);
        error_log("OSS Config used - Bucket: {$ossConfig['bucket']}, Endpoint: {$ossConfig['endpoint']}");
    }
    
    // Return success response (even if OSS upload failed, DB save was successful)
    $response = [
        'success' => true, 
        'message' => 'Pengajuan berhasil dikirim!',
        'application_id' => $insertedId,
        'oss_uploaded' => $ossUploadSuccess
    ];
    
    if ($ossUploadSuccess) {
        $response['oss_url'] = $ossUrl;
    } else {
        $response['oss_error'] = $ossError ?: 'OSS upload failed';
    }
    
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to submit application: ' . $e->getMessage()
    ]);
}
?>

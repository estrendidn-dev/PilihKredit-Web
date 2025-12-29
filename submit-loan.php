<?php
header('Content-Type: application/json');

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
if (empty($data['pinjaman_non_berjaminan']) || !is_array($data['pinjaman_non_berjaminan']) || count($data['pinjaman_non_berjaminan']) === 0) {
    echo json_encode(['success' => false, 'message' => "Field 'pinjaman_non_berjaminan' is required"]);
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
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pengajuan berhasil dikirim!',
        'application_id' => $insertedId
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to submit application: ' . $e->getMessage()
    ]);
}
?>

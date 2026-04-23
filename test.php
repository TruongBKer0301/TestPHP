<?php
header('Content-Type: application/json; charset=UTF-8');

$registrationNumber = isset($_GET['registration_number']) ? trim((string) $_GET['registration_number']) : '';
$searchResult = null;
$searchError = '';

$subjectLabels = [
    'toan' => 'Toán',
    'ngu_van' => 'Ngữ văn',
    'ngoai_ngu' => 'Ngoại ngữ',
    'vat_li' => 'Vật lý',
    'hoa_hoc' => 'Hóa học',
    'sinh_hoc' => 'Sinh học',
    'lich_su' => 'Lịch sử',
    'dia_li' => 'Địa lý',
    'gdcd' => 'GDCD',
    'ma_ngoai_ngu' => 'Mã ngoại ngữ',
];

if ($registrationNumber === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Vui lòng nhập số báo danh.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$csvPath = __DIR__ . '/diem_thi_thpt_2024.csv';

if (!is_file($csvPath) || !is_readable($csvPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Khong the doc file du lieu CSV.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$handle = fopen($csvPath, 'r');

if ($handle === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Khong mo duoc file CSV.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$headers = fgetcsv($handle);

if ($headers === false) {
    fclose($handle);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'File CSV rong hoac sai dinh dang.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) !== count($headers)) {
        continue;
    }

    $assocRow = array_combine($headers, $row);
    if ($assocRow === false) {
        continue;
    }

    if (isset($assocRow['sbd']) && trim((string) $assocRow['sbd']) === $registrationNumber) {
        $searchResult = $assocRow;
        break;
    }
}

fclose($handle);

if ($searchResult === null) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Khong tim thay thi sinh voi so bao danh nay.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$scores = [];
foreach ($subjectLabels as $key => $label) {
    $value = isset($searchResult[$key]) ? trim((string) $searchResult[$key]) : '';
    $scores[] = [
        'key' => $key,
        'label' => $label,
        'value' => $value !== '' ? $value : '-',
    ];
}

echo json_encode([
    'success' => true,
    'registration_number' => (string) $searchResult['sbd'],
    'scores' => $scores,
], JSON_UNESCAPED_UNICODE);
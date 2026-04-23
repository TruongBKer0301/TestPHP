<?php
$registrationNumber = isset($_GET['registration_number']) ? trim((string) $_GET['registration_number']) : '';
$searchResult = null;
$searchError = '';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($registrationNumber !== '') {
    $csvPath = __DIR__ . '/diem_thi_thpt_2024.csv';

    if (!is_file($csvPath) || !is_readable($csvPath)) {
        $searchError = 'Khong the doc file du lieu CSV.';
    } else {
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $searchError = 'Khong mo duoc file CSV.';
        } else {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                $searchError = 'File CSV rong hoac sai dinh dang.';
            } else {
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

                if ($searchResult === null && $searchError === '') {
                    $searchError = 'Khong tim thay thi sinh voi so bao danh nay.';
                }
            }

            fclose($handle);
        }
    }
}

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

$detailSection = '';

if ($registrationNumber === '') {
    $detailSection = '<p class="detail-text">Nhập số báo danh để xem chi tiết điểm.</p>';
} elseif ($searchError !== '') {
    $detailSection = '<div class="alert alert-danger mb-0" role="alert">' . h($searchError) . '</div>';
} else {
    $rows = '';

    foreach ($subjectLabels as $key => $label) {
        $value = isset($searchResult[$key]) ? trim((string) $searchResult[$key]) : '';
        $displayValue = $value !== '' ? $value : '-';

        $rows .= '<tr><td>' . h($label) . '</td><td>' . h($displayValue) . '</td></tr>';
    }

    $detailSection =
        '<p class="result-title">Kết quả cho SBD: <strong>' . h((string) $searchResult['sbd']) . '</strong></p>' .
        '<div class="table-responsive">' .
        '<table class="table table-bordered score-table">' .
        '<thead><tr><th style="width: 60%;">Môn học</th><th>Điểm</th></tr></thead>' .
        '<tbody>' . $rows . '</tbody>' .
        '</table>' .
        '</div>';
}

$templatePath = __DIR__ . '/template.html';

if (!is_file($templatePath) || !is_readable($templatePath)) {
    http_response_code(500);
    echo 'Khong the doc file giao dien template.html.';
    exit;
}

$template = file_get_contents($templatePath);

if ($template === false) {
    http_response_code(500);
    echo 'Khong tai duoc noi dung template.html.';
    exit;
}

echo strtr(
    $template,
    [
        '{{registrationNumber}}' => h($registrationNumber),
        '{{detailSection}}' => $detailSection,
    ]
);
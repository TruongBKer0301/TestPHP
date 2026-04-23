<?php
$registrationNumber = isset($_GET['registration_number']) ? trim((string) $_GET['registration_number']) : '';
$searchResult = null;
$searchError = '';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-Scores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #ededf0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .topbar {
            background: #182a90;
            height: 98px;
            border-radius: 0 0 3px 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar h1 {
            color: #fff;
            font-size: 42px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .app-shell {
            display: flex;
            gap: 52px;
            margin-top: 0;
        }

        .sidebar {
            width: 210px;
            min-height: calc(100vh - 98px);
            padding: 36px 18px;
            border-radius: 0 8px 0 0;
            background: linear-gradient(180deg, #ffdf00 0%, #8da943 56%, #2f8ea8 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .menu-title {
            text-align: center;
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 26px;
            color: #151515;
        }

        .sidebar-nav {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-nav li {
            margin: 18px 0;
        }

        .sidebar-nav a {
            text-decoration: none;
            font-size: 25px !important;
            color: #101010;
            font-weight: 400;
        }

        .sidebar-nav a.active {
            font-weight: 700;
        }

        .main-content {
            flex: 1;
            padding-right: 30px;
            padding-top: 34px;
            max-width: 1160px;
        }

        .content-card {
            background: #f4f4f6;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            padding: 40px 30px;
            margin-bottom: 34px;
        }

        .content-card h2 {
            font-size: 43px;
            font-weight: 700;
            margin-bottom: 26px;
            color: #0a0a0a;
        }

        .form-label {
            font-size: 32px;
            color: #111;
            margin-bottom: 8px;
        }

        .form-control {
            max-width: 650px;
            height: 66px;
            font-size: 24px;
        }

        .btn-submit {
            min-width: 160px;
            height: 66px;
            font-size: 26px;
            font-weight: 600;
            background: #000;
            border: none;
        }

        .detail-text {
            font-size: 34px;
            margin: 0;
        }

        .result-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .score-table {
            margin-bottom: 0;
            font-size: 20px;
        }

        .score-table thead th {
            background: #e7e9ef;
        }

        @media (max-width: 1200px) {
            .topbar h1 {
                font-size: 34px;
            }

            .menu-title {
                font-size: 28px;
            }

            .sidebar-nav a {
                font-size: 24px;
            }

            .content-card h2 {
                font-size: 33px;
            }

            .form-label {
                font-size: 25px;
            }

            .form-control,
            .btn-submit {
                height: 56px;
                font-size: 20px;
            }

            .detail-text {
                font-size: 28px;
            }

            .result-title {
                font-size: 24px;
            }

            .score-table {
                font-size: 18px;
            }
        }

        @media (max-width: 992px) {
            .app-shell {
                flex-direction: column;
                gap: 0;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
                border-radius: 0;
            }

            .sidebar-nav {
                display: flex;
                flex-wrap: wrap;
                gap: 12px 28px;
                justify-content: center;
            }

            .sidebar-nav li {
                margin: 0;
            }
            .main-content {
                padding: 20px;
            }

            .detail-text,
            .result-title {
                font-size: 22px;
            }

            .score-table {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <h1>G-Scores</h1>
    </header>

    <div class="app-shell">
        <aside class="sidebar">
            <h2 class="menu-title">Menu</h2>
            <ul class="sidebar-nav">
                <li><a href="#">Dashboard</a></li>
                <li><a href="#" class="active">Search Scores</a></li>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </aside>


        <main class="main-content">
            <section class="content-card">
                <h2>User Registration</h2>
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-lg-7 col-md-8 col-12">
                        <label for="registrationNumber" class="form-label">Registration Number:</label>
                        <input
                            type="text"
                            id="registrationNumber"
                            name="registration_number"
                            class="form-control"
                            placeholder="Enter registration number"
                            value="<?php echo htmlspecialchars($registrationNumber, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                    <div class="col-lg-auto col-md-auto col-12">
                        <button type="submit" class="btn btn-dark btn-submit">Submit</button>
                    </div>
                </form>
            </section>

            <section class="content-card">
                <h2>Detailed Scores</h2>
                <?php if ($registrationNumber === ''): ?>
                    <p class="detail-text">Nhập số báo danh để xem chi tiết điểm.</p>
                <?php elseif ($searchError !== ''): ?>
                    <div class="alert alert-danger mb-0" role="alert">
                        <?php echo htmlspecialchars($searchError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <p class="result-title">
                        Kết quả cho SBD: <strong><?php echo htmlspecialchars($searchResult['sbd'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered score-table">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Môn học</th>
                                    <th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectLabels as $key => $label): ?>
                                    <?php
                                    $value = isset($searchResult[$key]) ? trim((string) $searchResult[$key]) : '';
                                    $displayValue = $value !== '' ? $value : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
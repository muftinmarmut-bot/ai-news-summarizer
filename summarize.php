<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

$config = require __DIR__ . '/config.php';
$apiToken = $config['REPLICATE_API_TOKEN'];

$newsText = trim($_POST["news_text"]);
$mode = $_POST["mode"];

// Buat prompt
$prompt = "Kamu adalah asisten peringkas berita.\n"
        . "Ringkas teks berikut dalam bahasa Indonesia yang mudah dipahami.\n"
        . "Buat ringkasan dalam $mode:\n\n"
        . "Teks berita:\n\"$newsText\"";

$data = [
    "version" => "ibm-granite/granite-3.3-8b-instruct",
    "input" => ["prompt" => $prompt]
];

// Panggil API Replicate
$ch = curl_init("https://api.replicate.com/v1/predictions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Token $apiToken",
    "Content-Type: application/json",
    "Prefer: wait"
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if ($response === false) {
    die("cURL Error: " . curl_error($ch));
}
curl_close($ch);

$result = json_decode($response, true);
$output = isset($result["output"]) ? (is_array($result["output"]) ? implode("\n", $result["output"]) : $result["output"]) : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Hasil Ringkasan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 60px auto;
      background-color: #ffffff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    h1 {
      text-align: center;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    .output {
      white-space: pre-wrap;
      border: 1px solid #ddd;
      padding: 20px;
      border-radius: 8px;
      background-color: #fefefe;
      font-size: 16px;
      line-height: 1.6;
    }

    .back-link {
      display: block;
      margin-top: 30px;
      text-align: center;
      font-weight: 600;
      color: #2c3e50;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    footer {
      text-align: center;
      margin-top: 40px;
      font-size: 14px;
      color: #888;
    }

    @media (prefers-color-scheme: dark) {
      body {
        background-color: #1e1e1e;
        color: #f0f0f0;
      }

      .container {
        background-color: #2c2c2c;
        box-shadow: 0 8px 20px rgba(255, 255, 255, 0.05);
      }

      .output {
        background-color: #3a3a3a;
        color: #f0f0f0;
        border: 1px solid #555;
      }

      .back-link {
        color: #4a90e2;
      }

      footer {
        color: #aaa;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Hasil Ringkasan</h1>
    <?php if ($output): ?>
      <div class="output"><?= htmlspecialchars($output) ?></div>
    <?php else: ?>
      <h3>Tidak ada output langsung dari API.</h3>
      <pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    <?php endif; ?>
    <a class="back-link" href="index.html">← Kembali ke Halaman Utama</a>
  </div>
  <footer>
    &copy; 2025 AI News Summarizer. Dibuat oleh Ling. Semua hak dilindungi.
  </footer>
</body>
</html>
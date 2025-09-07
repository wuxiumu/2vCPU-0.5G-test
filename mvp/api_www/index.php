<?php
header('Content-Type: application/json; charset=utf-8');

// 可配置目录
$defaultDir = '/etc/nginx/sites-available/';
$dir = isset($_GET['dir']) ? $_GET['dir'] : $defaultDir;
$dir = rtrim($dir, '/') . '/';

// 记录开始时间
$start = microtime(true);

$result = [
    'dir' => $dir,
    'list_sites' => 'list_sites.php',
    'files' => [],
    'start_time' => date('Y-m-d H:i:s.u', (int)$start) . sprintf('.%03d', ($start - floor($start)) * 1000),
];

if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        if (is_file($dir . $f)) $result['files'][] = $f;
    }
    $result['success'] = true;
    $result['count'] = count($result['files']);
} else {
    $result['success'] = false;
    $result['error'] = "目录不存在或不可访问: $dir";
}

// 记录结束时间
$end = microtime(true);
$result['end_time'] = date('Y-m-d H:i:s.u', (int)$end) . sprintf('.%03d', ($end - floor($end)) * 1000);
$result['duration_ms'] = round(($end - $start) * 1000, 3);

// 输出JSON
echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
<?php
header('Content-Type: application/json');

// 系统信息
$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : php_uname();
$hostname = gethostname();
$kernel_version = php_uname('r');

// PHP信息
$php_sapi = php_sapi_name();
$php_memory_limit = ini_get('memory_limit');
$php_post_max_size = ini_get('post_max_size');
$php_upload_max_filesize = ini_get('upload_max_filesize');
$php_loaded_extensions = get_loaded_extensions();
$php_disabled_functions = array_filter(array_map('trim', explode(',', ini_get('disable_functions'))));

$common_extensions = [
    'curl', 'mbstring', 'gd', 'pdo_mysql', 'mysqli', 'openssl', 'fileinfo'
];
$extensions_status = [];
foreach ($common_extensions as $ext) {
    $extensions_status[$ext] = extension_loaded($ext);
}

// DB支持
$db_support = [
    'mysql' => extension_loaded('mysql') || extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
    'mariadb' => extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
    'pgsql' => extension_loaded('pgsql') || extension_loaded('pdo_pgsql'),
];

// 常用函数检测
$common_functions = [
    'curl_init', 'mb_strlen', 'file_get_contents', 'exec', 'shell_exec'
];
$functions_status = [];
foreach ($common_functions as $fn) {
    $functions_status[$fn] = function_exists($fn) && is_callable($fn);
}

// CPU load
$load = sys_getloadavg();

// CPU cores
if (function_exists('shell_exec')) {
    $cpu_cores = (int) shell_exec("nproc 2>/dev/null");
    if ($cpu_cores < 1) {
        $cpu_cores = 1;
    }
} else {
    $cpu_cores = 1;
}

// 内存
$meminfo = @file_get_contents('/proc/meminfo');
preg_match('/MemTotal:\s+(\d+)/', $meminfo, $mem_total);
preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $mem_avail);
$mem_total = isset($mem_total[1]) ? (int)$mem_total[1] : 0;
$mem_avail = isset($mem_avail[1]) ? (int)$mem_avail[1] : 0;

// swap
$swap_total = 0;
$swap_free = 0;
if ($meminfo) {
    preg_match('/SwapTotal:\s+(\d+)/', $meminfo, $swap_total_m);
    preg_match('/SwapFree:\s+(\d+)/', $meminfo, $swap_free_m);
    $swap_total = isset($swap_total_m[1]) ? (int)$swap_total_m[1] : 0;
    $swap_free = isset($swap_free_m[1]) ? (int)$swap_free_m[1] : 0;
}

// 硬盘
$disk_total = @disk_total_space('/');
$disk_free = @disk_free_space('/');

// 网络（仅保留原有兼容性，不输出详细信息）

// uptime
$uptime = @file_get_contents('/proc/uptime');
$uptime = $uptime ? intval(explode(' ', $uptime)[0]) : 0;

// 主要进程检测
$process_names = [
    'nginx' => 'nginx',
    'php-fpm' => 'php-fpm',
    'mysql' => 'mysqld'
];
$process_status = [];
foreach ($process_names as $label => $pname) {
    $process_status[$label] = false;
    if (function_exists('shell_exec')) {
        $ps = @shell_exec("ps aux | grep $pname | grep -v grep");
        if ($ps && strlen($ps) > 0) {
            $process_status[$label] = true;
        }
    }
}

$result = [
    'time' => date('Y-m-d H:i:s'),
    // 兼容原有字段
    'os' => php_uname(),
    'php_version' => PHP_VERSION,
    'load' => $load,
    'memory' => [
        'total' => $mem_total,
        'available' => $mem_avail,
        'used' => $mem_total - $mem_avail,
    ],
    'disk' => [
        'total' => $disk_total,
        'free' => $disk_free,
        'used' => $disk_total - $disk_free,
    ],
    'uptime_seconds' => $uptime,
    // 扩展字段
    'server' => [
        'server_software' => $server_software,
        'hostname' => $hostname,
        'kernel_version' => $kernel_version,
    ],
    'php' => [
        'sapi' => $php_sapi,
        'memory_limit' => $php_memory_limit,
        'post_max_size' => $php_post_max_size,
        'upload_max_filesize' => $php_upload_max_filesize,
        'loaded_extensions' => $php_loaded_extensions,
        'disabled_functions' => $php_disabled_functions,
        'extensions_status' => $extensions_status,
    ],
    'cpu' => [
        'loadavg' => $load,
        'cores' => $cpu_cores,
    ],
    'swap' => [
        'total' => $swap_total,
        'free' => $swap_free,
        'used' => $swap_total - $swap_free,
    ],
    'network' => [], // 保持结构
    'process' => $process_status,
    'db' => $db_support,
    'function_test' => $functions_status,
];

echo json_encode($result);
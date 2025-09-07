<?php
// 默认扫描目录，可通过 ?dir=/path/to/xxx 配置
$defaultDir = '/etc/nginx/sites-available/';
// $dir = isset($_GET['dir']) ? $_GET['dir'] : $defaultDir;
$dir = $defaultDir;
$dir = rtrim($dir, '/') . '/';
$files = [];
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        if (is_file($dir . $f)) $files[] = $f;
    }
} else {
    $error = "目录不存在或不可访问: $dir";
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>配置文件列表</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { background: linear-gradient(135deg,#f8fafc,#f2f6ff 100%); font-family: -apple-system,BlinkMacSystemFont,"SF Pro Display","Helvetica Neue",Arial,sans-serif; margin:0; }
    .container { max-width: 520px; margin: 50px auto; padding: 32px 26px 24px 26px; background: #fff; border-radius: 24px; box-shadow: 0 8px 40px #e0eaff18; }
    h1 { text-align: center; font-size: 1.7rem; font-weight: bold; margin-bottom: 20px; color: #222; }
    .desc { color: #777; text-align: center; margin-bottom: 24px; font-size: 1.03em;}
    .site-list { list-style: none; padding: 0; margin: 0; }
    .site-list li { margin: 0 0 18px 0; }
    .site-link {
      display: block; padding: 14px 18px; border-radius: 12px;
      background: #f5f8fa; color: #007aff; font-weight: 500;
      font-size: 1.15em; text-decoration: none; letter-spacing: .01em;
      box-shadow: 0 2px 12px #e4edff09; transition: background .15s,color .18s;
      border: 1.3px solid #e6eaf0;
    }
    .site-link:hover { background: #eaf3ff; color: #0054bf; }
    .logo { display: block; margin: 0 auto 26px auto; max-width: 72px; }
    .err { color: #ff3b30; text-align: center; margin: 18px 0 8px 0; font-size: 1.07em; }
    .form-bar {text-align:center;margin-bottom:22px;}
    .input-dir {
      width: 85%; font-size: 1em; border-radius: 8px; border: 1.2px solid #c3cbe1;
      padding: 7px 14px; outline: none; background: #f8fafd; transition: border-color .18s;
      margin-right:6px; max-width:320px;
    }
    .input-dir:focus { border-color: #007aff; }
    .btn-go {
      padding: 8px 18px; border-radius: 8px; border: none; background: #007aff;
      color: #fff; font-weight: 700; font-size: 1.02em; cursor: pointer;
      box-shadow: 0 1px 4px #b3dfff15; transition: filter .13s;
    }
    .btn-go:active { filter: brightness(0.97); }
  </style>
</head>
<body>
<div class="container">
  <img class="logo" src="https://img20.360buyimg.com/openfeedback/jfs/t1/328719/36/17066/56766/68bda4d9F886063fc/1d01b115131a1a7e.png" alt="logo" />
  <h1>配置文件列表</h1>
  <div class="desc">当前路径：<b><?=htmlspecialchars($dir)?></b></div>
  <form class="form-bar" method="get" action="" style="display:none;">
    <input class="input-dir" type="text" name="dir" value="<?=htmlspecialchars($dir)?>" placeholder="输入配置目录路径" />
    <button class="btn-go" type="submit">切换目录</button>
  </form>
  <?php if (!empty($error)): ?>
    <div class="err"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>
  <ul class="site-list">
    <?php foreach($files as $f): ?>
      <li>
        <a class="site-link" href="http://<?=htmlspecialchars($f)?>" target="_blank" rel="noopener"><?=htmlspecialchars($f)?></a>
      </li>
    <?php endforeach; ?>
    <?php if (empty($files) && empty($error)): ?>
      <li><span class="err">（无文件）</span></li>
    <?php endif; ?>
  </ul>
</div>
</body>
</html>
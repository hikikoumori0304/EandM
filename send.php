<?php
session_start();

/* ---- 1. ワンタイムトークン検証 ---- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
 || empty($_POST['__token'])
 || empty($_SESSION['form_token'])
 || !hash_equals($_SESSION['form_token'], $_POST['__token'])) {
    header('Content-Type: text/html; charset=UTF-8', true, 400);
    echo '<p style="padding:2rem;text-align:center;">不正な送信です。ページを再読み込みしてやり直してください。</p>';
    exit;
}
unset($_SESSION['form_token']);  // 使い捨て

/* ---- 1b. サーバー側バリデーション（長さ・制御文字） ---- */
$MAX_LEN = 2000;                          // 1項目あたり許可する最大文字数
foreach ($_POST as $k => $v) {
    if (in_array($k, ['__labels','__replyto','__token'], true)) continue;
    $vals = is_array($v) ? $v : [$v];

    foreach ($vals as $sv) {
        if (mb_strlen($sv) > $MAX_LEN) {
            header('Content-Type: text/html; charset=UTF-8', true, 413);
            echo '<p style="padding:2rem;text-align:center;">入力が長すぎます（最大 '.$MAX_LEN.' 文字）。</p>';
            exit;
        }
        /* 制御文字やヌルバイト混入防止 */
        if (preg_match('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/u', $sv)) {
            header('Content-Type: text/html; charset=UTF-8', true, 400);
            echo '<p style="padding:2rem;text-align:center;">不正な文字が含まれています。</p>';
            exit;
        }
    }
}

/* ========= ここだけ編集してください ========= */
$adminMail        = 'info@example.com';            // 管理者メールアドレス
$adminSubject     = '【サイト問合せ】内容通知';    // 管理者宛 件名
$userSubject      = 'お問い合わせありがとうございます'; // 自動返信 件名
$userHeaderLead   = "以下の内容で受け付けました。\n\n"; // 自動返信 文頭
$signature        = <<< 'SIG'
──────────────────────
サンプル株式会社
https://example.com/
──────────────────────
SIG;
$enableUserReply  = true;                          // true: 自動返信を送信 / false: 送信しない
/* ============================================ */

/* ---- 1c. 必須項目未入力チェック ---- */
$requiredKeys = array_filter(explode(',', $_POST['__required'] ?? ''));
foreach ($requiredKeys as $rk){
    $val = $_POST[$rk] ?? '';
    $isEmpty = is_array($val)
        ? count(array_filter($val,'strlen')) === 0
        : trim($val) === '';
    if ($isEmpty){
        header('Content-Type: text/html; charset=UTF-8', true, 400);
        echo '<p style="padding:2rem;text-align:center;">必須項目が未入力です。</p>';
        exit;
    }
}

/* ---- 1d. メールアドレス形式チェック ---- */
$emailKeys = array_filter(explode(',', $_POST['__replyto'] ?? ''));
foreach ($emailKeys as $ek){
    $v = $_POST[$ek] ?? '';
    if ($v !== '' && !filter_var(is_array($v)?($v[0]??''):$v, FILTER_VALIDATE_EMAIL)){
        header('Content-Type: text/html; charset=UTF-8', true, 400);
        echo '<p style="padding:2rem;text-align:center;">メールアドレスの形式が正しくありません。</p>';
        exit;
    }
}

/* ---- 2. IP 連投 30 秒制限 ---- */

/* クライアント IP 取得（X-Forwarded-For 優先） */
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = array_filter(array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
    $ip  = $ips[0];        // 先頭＝元のクライアント
} else {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
}
$ip = preg_replace('/[^0-9a-fA-F:.]/', '_', $ip);  // ファイル名用にサニタイズ

$stampFile = sys_get_temp_dir().'/form_last_'.preg_replace('/[^0-9a-fA-F:]/','_',$ip);
$now = time();
if (file_exists($stampFile)) {
    $elapsed = $now - (int)file_get_contents($stampFile);
    if ($elapsed < 30) {
        $wait = 30 - $elapsed;
        header('Content-Type: text/html; charset=UTF-8', true, 429);
        echo '<p style="padding:2rem;text-align:center;">30秒以内の連続送信はできません。少し時間をあけて送信して下さい。</p>';
        exit;
    }
}
file_put_contents($stampFile, (string)$now);

/* ---- 3. 送信処理 ---- */
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function ew($s,$suf){ return substr($s,-strlen($suf)) === $suf; }

$labels = json_decode($_POST['__labels'] ?? '[]', true);
$done   = [];
$lines  = [];

foreach ($_POST as $k => $v){
    if (in_array($k, ['__labels','__replyto','__required','__token'], true) || in_array($k, $done, true)) continue;

    /* 住所（3 項目とも空なら非表示） */
    if (ew($k, '_zip')){
        $b    = substr($k, 0, -4);
        $zip  = $_POST[$b.'_zip']  ?? '';
        $pref = $_POST[$b.'_pref'] ?? '';
        $addr = $_POST[$b.'_addr'] ?? '';

        if (trim($zip.$pref.$addr) === ''){
            $done = array_merge($done, ["{$b}_zip","{$b}_pref","{$b}_addr"]);
            continue;
        }

        $lines[] = "[住所] {$zip} / {$pref}{$addr}";
        $done = array_merge($done, ["{$b}_zip","{$b}_pref","{$b}_addr"]);
        continue;
    }

    /* 年月日（3 項目とも空なら非表示） */
    if (ew($k, '_y')){
        $b = substr($k, 0, -2);
        $y = $_POST[$b.'_y'] ?? '';
        $m = $_POST[$b.'_m'] ?? '';
        $d = $_POST[$b.'_d'] ?? '';

        if (trim($y.$m.$d) === ''){
            $done = array_merge($done, ["{$b}_y","{$b}_m","{$b}_d"]);
            continue;
        }

        $lines[] = "[日付] {$y}年{$m}月{$d}日";
        $done = array_merge($done, ["{$b}_y","{$b}_m","{$b}_d"]);
        continue;
    }

    /* 月日（2 項目とも空なら非表示） */
    if (ew($k, '_d_m')){
        $b = substr($k, 0, -4);
        $m = $_POST[$b.'_d_m'] ?? '';
        $d = $_POST[$b.'_d_d'] ?? '';

        if (trim($m.$d) === ''){
            $done = array_merge($done, ["{$b}_d_m","{$b}_d_d"]);
            continue;
        }

        $lines[] = "[日付] {$m}月{$d}日";
        $done = array_merge($done, ["{$b}_d_m","{$b}_d_d"]);
        continue;
    }

    /* 通常（空なら非表示） */
    $vals = is_array($v) ? array_filter($v,'strlen') : [trim($v)];
    if (count($vals) === 0 || $vals[0] === '') continue;

    $lines[] = '[' . ($labels[$k] ?? $k) . '] ' . (is_array($v) ? implode(', ', $vals) : $vals[0]);
}

/* ---- 宛先抽出（自動返信用） ---- */
$userEmail = '';
$keys = array_filter(explode(',', $_POST['__replyto'] ?? ''));
foreach ($keys as $k){
    if (!isset($_POST[$k])) continue;
    $candidate = is_array($_POST[$k]) ? ($_POST[$k][0] ?? '') : $_POST[$k];
    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)){
        $userEmail = $candidate; break;
    }
}

/* ---- 送信 ---- */
mb_language('Japanese'); mb_internal_encoding('UTF-8');

$bodyAdmin = "▼送信内容\n" . implode("\n", $lines) . "\n\n" . $signature;
mb_send_mail($adminMail, $adminSubject, $bodyAdmin, "From: {$adminMail}", "-f{$adminMail}");

if ($enableUserReply && $userEmail){
    $bodyUser = $userHeaderLead . implode("\n", $lines) . "\n\n" . $signature;
    mb_send_mail($userEmail, $userSubject, $bodyUser, "From: {$adminMail}", "-f{$adminMail}");
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>株式会社Ｅ＆Ｍ　公式ＨＰ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="株式会社Ｅ＆Ｍ　公式ＨＰ">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div id="container">



<!--▼▼▼▼▼ここから「ヘッダー」-->
<header>

<h1 id="logo"><a href="index.html"><img src="images/logo.svg" alt="株式会社Ｅ＆Ｍ"></a></h1>

<nav>
<ul>
<li><a href="index.html#kodawari">「関わる人、すべての笑顔のために」</a></li>
<li><a href="index.html#brand">事業内容</a>
	<ul>
	<li><a href="index.html#brand_01">化粧品原材料卸 </a></li>
	<li><a href="index.html#brand_02">自社ブランド開発・企画・販売</a></li>
	<li><a href="index.html#brand_03">その他コンサルティングサービス</a></li>
	</ul>
</li>
<li><a href="index.html#Product">Ferneの商品紹介</a></li>
<li><a href="index.html#koe">お客様の声</a></li>
<li><a href="index.html#faq">よく頂く質問</a></li>
<li><a href="contact.html">お問い合わせ</a></li>
<li><a href="https://retailer.orosy.com/brand/ac6a3cb1-56d6-4590-b570-efdee96eff35" target="_blank">オンラインショップ</a></li>
</ul>
</nav>

</header>
<!--▲▲▲▲▲ここまで「ヘッダー」-->



<main>



<!--▼▼▼▼▼ここから「お問い合わせフォーム（※サンプル画面）」-->
<section class="space-large">

<form>

<h2>送信完了</h2>
<p>送信を受け付けました。</p>

</form>

</section>
<!--▲▲▲▲▲ここまで「お問い合わせフォーム（※サンプル画面）」-->



</main>



<!--▼▼▼▼▼ここから「フッター」-->
<footer>

<div class="text">

<div>
<h4>株式会社Ｅ＆Ｍ</h4>
<p>
〒650-0011  兵庫県 神戸市 中央区下山手通3-8-11<br>
電話番号  050-8889-3379<br>
営業時間  10:00〜17:00<br>
メールアドス  info@ferne.world<br>
本ページの管理責任者:公森 隆人<br>
</p>
</div>

<div>
<h4>Follow Us</h4>
<ul class="icons">
<li><a href="https://x.com/ferneoffical?s=11"><i class="fa-brands fa-x-twitter"></i></a></li>
<li><a href="https://line.me/R/ti/p/@230bnnga"><i class="fab fa-line"></i></a></li>
<li><a href="https://www.instagram.com/ferne_official?igsh=cG0xcGdkN3RyYmwx"><i class="fab fa-instagram"></i></a></li>
</ul>
</div>

</div>

<div class="image">
<p class="logo"><img src="images/logo_kazari.svg" alt=""></p>
<small>&copy; E&M.co</small>
</div>

</footer>
<!--▲▲▲▲▲ここまで「フッター」-->



<!--テンプレートの著作。削除しないで下さい。-->
<span class="pr"><a href="https://template-party.com/" target="_blank">《Web Design:Template-Party》</a></span>



</div>
<!--/#container-->



<!--開閉ボタン（ハンバーガーアイコン）-->
<div id="menubar_hdr">
<div class="menu-icon">
<span></span><span></span>
</div>
</div>



<!--開閉ブロック-->
<div id="menubar">

<nav>
<ul>
<li><a href="#kodawari">私たちのこだわり</a></li>
<li><a href="#brand">製品ブランド</a>
	<ul>
	<li><a href="#">ブランド別メニュー</a></li>
	<li><a href="#">ブランド別メニュー</a></li>
	<li><a href="#">ブランド別メニュー</a></li>
	</ul>
</li>
<li><a href="#koe">お客様の声</a></li>
<li><a href="#faq">よく頂く質問</a></li>
<li><a href="contact.html">お問い合わせ</a></li>
<li><a href="#">オンラインショップ</a></li>
</ul>
</nav>

<p>ここのブロックは、htmlの下の方にある&lt;div id=&quot;menubar&quot;&gt;で編集できます。<br>
ヘッダーのメニューとは別のブロックになるので注意して下さい。</p>

</div>
<!--/#menubar-->



<!--jQueryの読み込み-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!--パララックス（inview）-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/protonet-jquery.inview/1.1.2/jquery.inview.min.js"></script>
<script src="js/jquery.inview_set.js"></script>

<!--このテンプレート専用のスクリプト-->
<script src="js/main.js"></script>

</body>
</html>

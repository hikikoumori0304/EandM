<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: form.php'); exit; }

/* ---- 1. トークン生成 ---- */
$token = bin2hex(random_bytes(16));
$_SESSION['form_token'] = $token;

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function ew($s,$suf){ return substr($s,-strlen($suf)) === $suf; }
$labels = json_decode($_POST['__labels'] ?? '[]', true);
$done   = [];
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

<form action="send.php" method="post"><h2>確認画面</h2>
<table class="ta1">
<?php foreach ($_POST as $k => $v):
    if ($k==='__labels' || $k==='__replyto' || $k==='__required' || in_array($k,$done,true)) continue;

    /* 住所まとめ（3 項目とも空なら非表示） */
    if (ew($k,'_zip')){
        $b    = substr($k,0,-4);
        $zip  = $_POST[$b.'_zip']  ?? '';
        $pref = $_POST[$b.'_pref'] ?? '';
        $addr = $_POST[$b.'_addr'] ?? '';

        if (trim($zip.$pref.$addr) === ''){
            $done = array_merge($done,["{$b}_zip","{$b}_pref","{$b}_addr"]);
            continue;
        }

        echo '<tr><th>'.h($labels[$k]??'住所').'</th><td>';
        echo h($zip).'<br>';
        echo h($pref.$addr).'</td></tr>';
        $done = array_merge($done,["{$b}_zip","{$b}_pref","{$b}_addr"]);
        continue;
    }

    /* 年月日まとめ（3 項目とも空なら非表示） */
    if (ew($k,'_y')){
        $b = substr($k,0,-2);
        $y = $_POST[$b.'_y'] ?? '';
        $m = $_POST[$b.'_m'] ?? '';
        $d = $_POST[$b.'_d'] ?? '';

        if (trim($y.$m.$d) === ''){
            $done = array_merge($done,["{$b}_y","{$b}_m","{$b}_d"]);
            continue;
        }

        $heading = preg_replace('/\s*[年月日]\z/u','',$labels[$k]??'');
        echo '<tr><th>'.h($heading).'</th><td>';
        echo h("{$y}年{$m}月{$d}日").'</td></tr>';
        $done = array_merge($done,["{$b}_y","{$b}_m","{$b}_d"]); continue;
    }

    /* 月日まとめ（2 項目とも空なら非表示） */
    if (ew($k,'_d_m')){
        $b = substr($k,0,-4);
        $m = $_POST[$b.'_d_m'] ?? '';
        $d = $_POST[$b.'_d_d'] ?? '';

        if (trim($m.$d) === ''){
            $done = array_merge($done,["{$b}_d_m","{$b}_d_d"]);
            continue;
        }

        $heading = preg_replace('/\s*[年月日]\z/u','',$labels[$k]??'');
        echo '<tr><th>'.h($heading).'</th><td>';
        echo h("{$m}月{$d}日").'</td></tr>';
        $done = array_merge($done,["{$b}_d_m","{$b}_d_d"]); continue;
    }

    /* 通常（空なら非表示） */
    $vals = is_array($v) ? array_filter($v,'strlen') : [trim($v)];
    if (count($vals) === 0 || $vals[0] === '') continue;

    echo '<tr><th>'.h($labels[$k]??$k).'</th><td>';
    foreach ($vals as $sv) echo nl2br(h($sv)).'<br>';
    echo '</td></tr>';
endforeach; ?>
</table>

<input type="hidden" name="__labels" value="<?=h($_POST['__labels'])?>">
<input type="hidden" name="__token"  value="<?=h($token)?>">
<?php foreach ($_POST as $k=>$v):
    if ($k==='__labels') continue;
    if (is_array($v)):
        foreach ($v as $sv)
            echo '<input type="hidden" name="'.$k.'[]" value="'.h($sv).'">';
    else:
        echo '<input type="hidden" name="'.$k.'" value="'.h($v).'">';
    endif;
endforeach; ?>
<p class="c">
    <button type="button" onclick="history.back()">修正する</button>
    <button type="submit">送信する</button>
</p>
</form>

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

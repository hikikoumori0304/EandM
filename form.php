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
<form action="confirm.php" method="post" id="genForm"><h2>お問い合わせフォーム</h2><table class="ta1">
<tr><th>お名前※<span class=\"req\">*</span></th><td><input type="text" name="f_68fd85cff20b7" class="wl" required /></td></tr>
<tr><th>メールアドレス※<span class=\"req\">*</span></th><td><input type="text" name="f_68fd85cff20c2" class="wl" required /></td></tr>
<tr><th>お問い合わせ詳細※<span class=\"req\">*</span></th><td><textarea name="f_68fd85cff20c7" rows="10" class="wl" required></textarea></td></tr>
</table><input type="hidden" name="__labels" value='{"f_68fd85cff20b7":"お名前※","f_68fd85cff20c2":"メールアドレス※","f_68fd85cff20c7":"お問い合わせ詳細※"}'>
<input type="hidden" name="__required" value="f_68fd85cff20b7,f_68fd85cff20c2,f_68fd85cff20c7">
<input type="hidden" name="__replyto" value="f_68fd85cff20c2">
<p class="c"><button type="submit">確認画面へ</button></p></form><script>
/* 郵便番号検索 */
document.addEventListener('click', e=>{
    if(!e.target.matches('.lookup')) return;
    const blk=e.target.closest('.addr-block');
    const zip=blk.querySelector('.zip').value.replace(/\D/g,'');
    if(zip.length!==7){alert('郵便番号を正しく入力してください');return;}
    fetch('https://zipcloud.ibsnet.co.jp/api/search?zipcode='+zip)
      .then(r=>r.json()).then(d=>{
          if(d.status===200&&d.results){
              const r=d.results[0];
              blk.querySelector('.pref').value  = r.address1;
              blk.querySelector('.addr1').value = r.address2+r.address3;
          }else alert('住所が見つかりません');
      }).catch(()=>alert('通信エラーで検索に失敗しました'));
});

/* チェックボックス 1 つ以上必須 */
document.getElementById('genForm').addEventListener('submit',e=>{
    for(const g of e.target.querySelectorAll('.chk-group[data-required="1"]')){
        if(!g.querySelector('input[type=checkbox]:checked')){
            alert('「'+g.dataset.label+'」を1つ以上選択してください');
            g.scrollIntoView({behavior:'smooth', block:'center'});
            e.preventDefault(); return;
        }
    }
});
</script>
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

<?php
# データベース処理機能を読み込み
require_once 'database.php';
require_once 'functions.php';

function content($c) {
	global $link;

# [img id=1 width=200]
	$expr = '/\[img id=(\d+) width=(\d+)\]/';
	$matches = [];

# https://www.php.net/manual/ja/function.preg-match-all.php
	preg_match_all($expr, $c, $matches);
	$tags = [];

	$i = 0;
	connect('sealabo');# 接続
	foreach($matches[1] as $id) {
		$img = select("SELECT `src`,`alt` FROM `tokyo_images` WHERE `id`={$id};");
		$tags[] = '<img src="' . $img[0]['src'] . '" alt="' . $img[0]['alt'] . '" width="' . $matches[2][$i++] . '">';
	}
	disconnect();# 切断
	$html = '';
	$i = 0;
	foreach(preg_split($expr, $c) as $text) {
		$html .= $text;
		if(!empty($tags[$i])) $html .= $tags[$i++];
	}

	return str_replace("\n", '<br>', $html);
}

# 変数宣言+初期化
$title = '';
$content = '';
$create = '';
$src = '';
$alt = '';

if(!empty($_GET['id'])) {
	connect('sealabo');# 接続
	$post = select('SELECT `tokyo_posts`.`title`,`tokyo_posts`.`content`,`tokyo_posts`.`created_at`,`tokyo_images`.`src`,`tokyo_images`.`alt` FROM `tokyo_posts` LEFT JOIN `tokyo_images` ON `tokyo_posts`.`icon`=`tokyo_images`.`id` WHERE `tokyo_posts`.`id`=' . (int)$_GET['id'] . ';');
	disconnect();# 切断
	if(count($post)) {
		$title = $post[0]['title'];
		$content = content($post[0]['content']);
		$create = timestamptoymd($post[0]['created_at']);
		$src = $post[0]['src'];
		$alt = $post[0]['alt'];
	}
}
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>HACHINOCO SEA LABORATORY</title>
<link rel="stylesheet" media="all" href="style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script src="script.js"></script>
</head>

<body>
<!--メインナビ-->
<nav id="mainnav">
	<p id="menuWrap"><a id="menu"><span id="menuBtn"></span></a></p>
	<div class="panel">
		<ul>
			<li><a href="/#header">TOP</a></li>
			<li><a href="/#WE">WE</a></li>
			<li><a href="/#BUSINESS">BUSINESS</a></li>
			<li><a href="/#CAREER">CAREER</a></li>
			<li><a href="https://ameblo.jp/gentekko/" target="_blank">BLOG</a></li>
			<li><a href="https://www.facebook.com/gentekko" target="_blank">FACEBOOK</a></li>
			<li><a href="mailto:gentekko&#64;gmail.com">MAIL</a></li>
		</ul>
	</div>
</nav>
<!--/メインナビ-->

<!--ページタイトル-->
<div id="article_header">
	<img src="images/seatop.png" alt="">
	<div id="slogan"></div>
</div>
<!--/ページタイトル-->

<div id="article">
	<div class="clearfix">
		<h1 class="left"><?php echo $title; ?></h1>
		<div class="right"><?php echo $create; ?></div>
	</div>
	<p><?php echo $content; ?></p>
</div>
</body>
</html>


<?php
# 現在のページ
$ARTICLE_PER_PAGE = 4;
$current = empty($_GET['page']) ? 1 : (int)$_GET['page'];
$offset = ($current - 1) * $ARTICLE_PER_PAGE;

# データベース処理機能を読み込み
require_once 'database.php';
require_once 'functions.php';

# 接続
connect("sealabo");

#記事一覧
# <a href="???"><img src="???"alt="" class="icon"><div class="dd"><div class="date">???</div><div class="title">???</div><div class="text">???</div><div></a>

# DB:SELECT
$getY = empty($_GET['y']) ? 0 : (int)$_GET['y'];
$getM = empty($_GET['m']) ? 0 : (int)$_GET['m'];
$getD = empty($_GET['d']) ? 0 : (int)$_GET['d'];
$y = $getY ? ' YEAR(`tokyo_posts`.`created_at`)=' . $getY : '';
$m = $getM ? ($y != '' ? ' AND' : '') . ' MONTH(`tokyo_posts`.`created_at`)=' . $getM : '';
$d = $getD ? ($m != '' ? ' AND' : '') . ' DATE(`tokyo_posts`.`created_at`)=' . $getD : '';

#右の条件かつ左の条件→&&
#$d = $getD ? ($y != '' && $m != '' ? ' AND' : '') . ' DATE(`tokyo_posts`.`created_at`)=' . $getD : '';

$where = $y != '' ? ' WHERE' . $y . $m . $d : '';
$posts = select('SELECT `tokyo_posts`.`id`,`tokyo_posts`.`title`,`tokyo_posts`.`created_at`,`tokyo_posts`.`content`,`tokyo_images`.`src`,`tokyo_images`.`alt` FROM `tokyo_posts` LEFT JOIN `tokyo_images` ON `tokyo_posts`.`icon`=`tokyo_images`.`id`' . $where . ' ORDER BY `tokyo_posts`.`id` DESC LIMIT ' . $ARTICLE_PER_PAGE . ' OFFSET ' . $offset . ';');
#$posts = select('SELECT `tokyo_posts`.`id`,`tokyo_posts`.`title`,`tokyo_posts`.`created_at`,IF(CHAR_LENGTH(`content`)>20,	CONCAT(LEFT(`content`,20), '...'),`content`),`tokyo_images`.`src`,`tokyo_images`.`alt` FROM `tokyo_posts` LEFT JOIN `tokyo_images` ON `tokyo_posts`.`icon`=`tokyo_images`.`id`' . $where . ' ORDER BY `tokyo_posts`.`id` DESC LIMIT ' . $ARTICLE_PER_PAGE . ' OFFSET ' . $offset . ';');

# 何ページから何ページまで 
# LIMIT ' . $ARTICLE_PER_PAGE . ' OFFSET ' . $offset . 
# LIMIT x（ex）1が押されたら1 ' OFFSET ' y（ex）x+20

/*ヒアドキュメント
<?php
$変数 = <<<終了の文字列
 
「文字列を記述」
 
終了の文字列;
?>

$sql_select_tokyo_posts = <<<AIUEO
SELECT `tokyo_posts`.`id`
  ,`tokyo_posts`.`title`
  ,`tokyo_posts`.`created_at`
  ,`tokyo_images`.`src`
  ,`tokyo_images`.`alt` 
FROM `tokyo_posts` 
LEFT JOIN `tokyo_images` 
ON `tokyo_posts`.`icon`=`tokyo_images`.`id`
$where 
ORDER BY `tokyo_posts`.`id` DESC 
LIMIT $ARTICLE_PER_PAGE OFFSET $offset;
AIUEO;

$posts = select($sql_select_posts);
*/

$ym = select('SELECT `tokyo_posts`.`created_at` FROM `tokyo_posts` LEFT JOIN `tokyo_images` ON `tokyo_posts`.`icon`=`tokyo_images`.`id`;');
$html = '';
$py = 0;
$pm = 0;

# text(未)
# HTML作成

# 2021-12-19 13:12:15
# PHPの日時データに変換
# フォーマットに従って取得
# Y-m-d -> 2021-12-19
# Y/m/d -> 2021/12/19
# Y/m/d H:i:s -> 2021/12/19 09:04:00
foreach($posts as $post) $html .= '<article><a href="tokyo_article.php?id=' . $post['id'] . '"><img src="' . ($post['src'] ?? 'images/blogimage.jpg') . '"alt="' . ($post['alt'] ?? '') . '" class="icon"><div class="dd"><div class="date">' . timestamptoymd($post['created_at']) . '</div><div class="title">' . h($post['title']) . '</div><div class="text">' . strcut($post['content'], 20, '...') . '</div></div></a></article>';
#foreach($posts as $post) $html .= '<article><a href="article.php?id=' . $post['id'] . '"><img src="' . ($post['src'] ?? 'images/blogimage.jpg') . '"alt="' . ($post['alt'] ?? '') . '" class="icon"><div class="dd"><div class="date">' . $where. '</div><div class="title">' . htmlspecialchars($post['title'], ENT_QUOTES) . '</div><div class="text">' . $substr_content . '</div></div></a></article>';
#($post['src'] ?? 'images/blogimage.jpg') → ?? →srcが入ってなかったらimages/blogimage.jpgを表示

#ページ送り
$pageCount = ceil(select('SELECT COUNT(1) AS c FROM `tokyo_posts` LEFT JOIN `tokyo_images` ON `tokyo_posts`.`icon`=`tokyo_images`.`id`' . $where . ';')[0]['c'] / 4);
$link->close();# 切断

$query = '';
if($y) {
	$query = 'y=' . $getY;
	if($m) $query .= '&m=' . $getM;
}
#$query .= $search ? ($query ? '&' : '') . 'input1=' . $search : '';
$page = '';
if($pageCount < 6) {
	for($i = 1; $i <= $pageCount; ++$i) {
		$pquery = '';
		if($i > 1) {
			$page .= '・';
			$pquery = ($query ? '&' : '') . 'page=' . $i;
		}
		$page .= $i != $current ? '<a href="?' . $query . $pquery . '">' . $i . '</a>' : $i;
	}
} else {
	if($current > 3) $page .= '<a href="?' . $query . '">1</a>…';
	$firstPage = max(1, $current - 2);
	$lastPage = min($firstPage + 4, $pageCount);
	for($i = $firstPage; $i <= $lastPage; ++$i) {
		if($i > $firstPage) $page .= '・';
		$pquery = $i > 1 ? ($query ? '&' : '') . 'page=' . $i : '';
		$page .= $i !== $current ? '<a href="?' . $query . $pquery . '">' . $i . '</a>' : $i;
	}
	$pquery = ($query ? '&' : '') . 'page=' . $pageCount;
	if($current < $pageCount - 2) $page .= '…<a href="?' . $query . $pquery . '">' . $pageCount . '</a>';
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
<div id ="header">
	<img src="images/seatop.png" alt="">
	<div id="slogan"><h1>TOKYO</h1></div>
</div>
<!--/ページタイトル-->

<!--取り組み一覧（繰り返し10件程度_flexbox_html14章）-->
<div><?php echo $html; ?></div>
<!--/取り組み一覧-->

<div id="pagenation"><?php echo $page; ?></div>

</body>
</html>
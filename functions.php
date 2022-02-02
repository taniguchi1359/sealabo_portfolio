<?php
# 文字列を任意の文字で左右どちらかを埋めて、指定した文字数(桁数)にする
function strpad($subject, $pad = '0', $count = 2, $isLeft = true) {
	$text = $subject;
	for($i = 0; $i < $count; ++$i) $text = $isLeft ? $pad . $text : ($text . $pad);
	return mb_substr($text, -$count);
}

# html特殊文字エスケープ
function h($text, $ent = ENT_QUOTES, $charset = NULL, $double = true) {
	return htmlspecialchars($text, $ent, $charset, $double);
}

# 文字列の先頭からcount文字切り取って、文字数が超過している場合は、指定した文字列($add)を付加
function strcut($subject, $count, $add) {
	return mb_substr($subject, 0, $count) . (mb_strlen($subject) > $count ? $add : '');
}

# 2021-01-26 13:53:00 -> 2021/01/26
function timestamptoymd($timestamp) {
	$date = date_parse($timestamp);
	return $date['year'] . '/' . strpad($date['month']) . '/' . strpad($date['day']);
}
?>
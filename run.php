#!/usr/local/bin/php
<?php

$keywords_to_watch = ['san jose', 'santa clara', 'bay area', '87', 'beretta', 'shadow', 'usp compact', 'uspc', 'glock 34', 'hk45', 'glock 19','sp-01'];

$buff = `curl -s --url 'http://www.calguns.net/calgunforum/forumdisplay.php?f=332' `;

//<td class="alt1" id="td_threadtitle_(\d+)" title="(.+)">\r

$str = preg_replace('|\s|', " ", $buff);

preg_match_all('|<td class="alt1" id="td_threadtitle_(\d+)" title=([^>]+)>|i', $str, $found_rows);

if(count($found_rows)> 0){
	for($i = 0; $i < count($found_rows[2]); $i++){
		$wts_decoded = html_entity_decode($found_rows[2][$i]);
		str_replace($keywords_to_watch, "AA", strtolower($wts_decoded), $replaces);
		if($replaces > 0) {
			$tidy_str = preg_replace('|\s+|', " ", $wts_decoded);
			print "FOUND: {$tidy_str}\nhttp://www.calguns.net/calgunforum/showthread.php?t={$found_rows[1][$i]}\n\n";
		}
	}
}
?>
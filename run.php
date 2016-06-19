#!/usr/local/bin/php
<?php
include( __dir__. '/config.php');
$input_file = __dir__. '/keywords_to_watch.txt';
$keywords_to_watch = [];

if(file_exists($input_file)){
	$buff = file($input_file);
	for($row_idx = 0; $row_idx < count($buff); $row_idx++){
		$keywords_to_watch[] = strtolower(trim($buff[$row_idx]));
	}
} else die("Keyword File Not Found.");

foreach($site_urls as $url){
	$buff = `curl -s --url '{$url}' `;

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
}
?>
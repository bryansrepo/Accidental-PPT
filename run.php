#!/usr/local/bin/php
<?php
include( __dir__. '/config.php');
$include_keyword_file = __dir__. '/keywords_to_watch.txt';
$exclude_keyword_file = __dir__. '/keywords_to_exclude.txt';

$DEBUG = 0;

$keywords_to_watch = [];
$keywords_to_skip = [];

if(file_exists($include_keyword_file)){
	$buff = file($include_keyword_file);
	for($row_idx = 0; $row_idx < count($buff); $row_idx++){
		$keywords_to_watch[] = strtolower(trim($buff[$row_idx]));
	}
} else die("Watch Keyword File Not Found.");

if(file_exists($exclude_keyword_file)){
	$buff = file($exclude_keyword_file);
	for($row_idx = 0; $row_idx < count($buff); $row_idx++){
		$keywords_to_skip[] = trim($buff[$row_idx]);
	}
}

foreach($site_urls as $url){
	$buff = `curl -s --url '{$url}' `;

	$str = preg_replace('|\s|', " ", $buff);

	preg_match_all('|<td class="alt1" id="td_threadtitle_(\d+)" title=([^>]+)>|i', $str, $found_rows);

	if(count($found_rows)> 0){
		for($i = 0; $i < count($found_rows[2]); $i++){
			
			if(count($keywords_to_skip)){
				$wts_decoded = html_entity_decode($found_rows[2][$i]);
				$replaces = 0;
				str_replace($keywords_to_skip, "AA", $wts_decoded, $replaces);
				if($replaces > 0){
					if($DEBUG) print "SKIPPING: {$wts_decoded}\n";
					continue;
				}
			}

			$wts_decoded = html_entity_decode($found_rows[2][$i]);

			$replaces = 0;
			str_replace($keywords_to_watch, "AA", strtolower($wts_decoded), $replaces);
			if($replaces > 0) {
				$tidy_str = preg_replace('|\s+|', " ", $wts_decoded);
				print "***\n{$tidy_str}\nhttp://www.calguns.net/calgunforum/showthread.php?t={$found_rows[1][$i]}\n\n";
			}
		}
	}
}
?>
#!/usr/local/bin/php
<?php
require __dir__. '/config.php';
$include_keyword_file = __dir__. '/keywords_to_watch.txt';
$exclude_keyword_file = __dir__. '/keywords_to_exclude.txt';

$DEBUG = 0;

$keywords_to_watch = [];
$keywords_to_skip = [];

if (file_exists($include_keyword_file)) {
    $buff = file($include_keyword_file);
    for ($row_idx = 0; $row_idx < count($buff); $row_idx++) {
        $keywords_to_watch[] = strtolower(trim($buff[$row_idx]));
    }
} else {
    die("Watch Keyword File Not Found.");
}

if (file_exists($exclude_keyword_file)) {
    $buff = file($exclude_keyword_file);
    for ($row_idx = 0; $row_idx < count($buff); $row_idx++) {
        $keywords_to_skip[] = trim($buff[$row_idx]);
    }
}

foreach ($site_urls as $url) {
    if ($url == "--") {
        print "\n##########################################################\n\n";
        continue;
    }

    $found_rows = [];
    $found_titles = [];
    $title_map = [];
     
    $buff = `curl -s --url '{$url}' `;

    $str = preg_replace('|\s|', " ", $buff);

    preg_match_all('|<td class="alt1" id="td_threadtitle_(\d+)" title=([^>]+)>|i', $str, $found_rows);
    preg_match_all('|<a href="showthread\.php\?s=.+?&amp;t=(\d+)" id="thread_title_\d+">([^<]+?)</a>|i', $str, $found_titles);

    // Create a map for the post titles
    
    foreach ($found_titles[1] as $idx => $f) {
        $title_map[$f] = $found_titles[2][$idx];
    }
    
    if (count($found_rows)> 0) {
        for ($i = 0; $i < count($found_rows[2]); $i++) {

            $thread_id = $found_rows[1][$i];
            
            if (count($keywords_to_skip)) {
                if (isset($title_map[$thread_id])) {
                    $this_title =  $title_map[$thread_id];
                } else {
                    $this_title = "";
                }
                
                $wts_decoded = html_entity_decode($this_title . " " . $found_rows[2][$i]);
                $replaces = 0;
                str_replace($keywords_to_skip, "AA", $wts_decoded, $replaces);
                if ($replaces > 0) {
                    if ($DEBUG) {
                        print "SKIPPING: {$wts_decoded}\n";
                    }
                    continue;
                }
            }

            $wts_decoded = html_entity_decode($this_title . " :: \n\n" . $found_rows[2][$i]);

            $replaces = 0;
            str_replace($keywords_to_watch, "AA", strtolower($wts_decoded), $replaces);
            if ($replaces > 0) {
                $tidy_str = preg_replace('|\s+|', " ", $wts_decoded);
                print "***\n\n{$tidy_str}\n\nhttp://www.calguns.net/calgunforum/showthread.php?t={$thread_id}\n\n";
            }
        }
    }
}
?>
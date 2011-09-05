<?php
echo date ("Y-m-d H:i:s");
die;

$active_page = 120;
$total_pages = 124;
echo "active page: $active_page of a total of $total_pages\n";

$pages = array();
if($total_pages <= 12) {
    for($page = 1; $page <= $total_pages; $page++) $pages[] = $page;
} elseif($active_page >= $total_pages - 8)
{
    for($page = 1; $page <= 2; $page++) $pages[] = $page;
    
    $left_limit = ($total_pages-$active_page <=2) ? -$total_pages+$active_page+6: 3;
    
    for($page = $active_page - $left_limit; $page <= $total_pages; $page++)
    if($page > 0 && $page <= $total_pages) $pages[] = $page;
} elseif($active_page <= 8) {
    
    $right_limit = ($active_page <= 2) ? 7-$active_page : 3;
    
    for($page = 1; $page <= $active_page+$right_limit; $page++) $pages[] = $page;
    
    for($page = $total_pages-1; $page <= $total_pages; $page++) $pages[] = $page;
} else {
    for($page = 1; $page <= 2; $page++) $pages[] = $page;
    
    for($page = $active_page-3; $page <= $active_page+3; $page++) $pages[] = $page;
    
    for($page = $total_pages-1; $page <= $total_pages; $page++) $pages[] = $page;
}


do {
echo current($pages);
echo " ";
//the order of the test below is ultimately important
if(current($pages) != next($pages)-1 && current($pages)) echo "... ";
} while(current($pages));

echo "\n\n";

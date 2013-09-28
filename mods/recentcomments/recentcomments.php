<?php
$page = new Page("Recent Comments");

$html = "";

$qry = new DBQuery();
$query = "SELECT * FROM kb3_comments ORDER BY posttime DESC LIMIT 23;";
$result = $qry->execute($query);

$comments = array();

while ($row = $qry->getRow())
{
   $comments[] = array('time' => $row['posttime'],
                       'name' => $row['name'],
                       'comment' => stripslashes($row['comment']),
                       'kll_id' => $row['kll_id'],
                       'id' => $row['id']);
}

global $smarty;
$smarty->assign_by_ref('comments', $comments);
$html .= $smarty->fetch('../../../mods/recentcomments/recentcomments.tpl');

$page->setContent($html);

$page->generate();
?>

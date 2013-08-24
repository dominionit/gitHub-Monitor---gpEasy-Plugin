<?php

defined('is_running') or die('Not an entry point...');



class gitHub_Monitor_Gadget{

	function gitHub_Monitor_Gadget(){
	    global $addonPathData;
		if (!file_exists($addonPathData.'/news.php')) {
		  echo '<h2>gitHub Update</h2>';
		  echo '<div>';
		   echo 'No updates received yet.';
		  echo '</div>';
		}
		include $addonPathData.'/news.php';
		
		echo '<div id="github-header"><span>Coding News</span></h2>';
		foreach($news as $newsItem) {
		  $dateS = date("F j, Y, g:i a",strtotime($newsItem['datetime'])); 
		  echo '<div>';
  		   echo "<span class='github-item-header'><a href='{$newsItem['url']}' title='{$newsItem['repo_name']}'>{$newsItem['repo_name']}</a></span><br/>";
		   echo "<span class='github-item-subheader'>$dateS - {$newsItem['committer']}</span><br/>";
		   echo "<span class='github-item-comment'>{$newsItem['comment']}</span><br/>";
		   echo "<span class='github-item-info'>{$newsItem['comment']}</span>";
		  echo '</div>';
		}
	}

}

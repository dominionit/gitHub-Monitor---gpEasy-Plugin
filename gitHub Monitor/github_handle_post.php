<?php
function github_handle_post($path){
   if (( $_SERVER['REMOTE_ADDR'] == '207.97.227.253') || 
       ( $_SERVER['REMOTE_ADDR'] == '50.57.128.197') ||
	   ( $_SERVER['REMOTE_ADDR'] == '108.171.174.178')) {
	    //okay a post from gitHub to us
		if (isset($_POST['payload'])) {
			$post_data = json_decode($_POST['payload']);
			$repo_name = $post_data['repository']['name'];
			global $addonPathData;
			if (!is_dir($addonPathData.'/'.$repo_name)) {
			  mkdir($addonPathData.'/'.$repo_name);
			}
			file_put_contents($addonPathData.'/'.$repo_name.'/notifications.php',$post_data);
			$fp = fopen($addonPathData.'/flock.php', "r+");
			if (flock($fp, LOCK_EX)) { // do an exclusive lock
			//This is now handeld as a critical section
			  $news  = array();
			 if (file_exists($addonPathData.'/news.php')) {
  			   include $addonPathData.'/news.php';
			 } else {
			   $news  = array('comment' => 'New entry');
             }			 
			 $news[] = array('comment' => $post_data['commits']['message']);
			 common::SaveArray($addonPathData.'/news.php','news',$news);
			 //Exit critical section
			 flock($fp, LOCK_UN); // release the lock
			 exit(); //we can stop everything, no need to let server get unwanted data back
			} 
		}	
  }	   
  
  return $path;
}
?>
<?php
function github_handle_post($path){
   if (( $_SERVER['REMOTE_ADDR'] == '207.97.227.253') || 
       ( $_SERVER['REMOTE_ADDR'] == '50.57.128.197') ||
	   ( $_SERVER['REMOTE_ADDR'] == '108.171.174.178') ||
	   ( $_SERVER['REMOTE_ADDR'] == '127.0.0.1')) {
	    //okay a post from gitHub to us
		if (isset($_POST['payload'])) {
			$post_data = json_decode($_POST['payload']);
			//error_log(print_r($post_data,true));
			$repo_name = $post_data->repository->name;

			global $addonPathData;

			$oldmask = umask(0755);
			gpFiles::SaveArray($addonPathData.'/'.$repo_name.'/'.$post_data->head_commit->['id'],'news',$post_data);
			//unmask($oldmask);

			$fp = fopen($addonPathData.'/flock.php', "c");
			chmod($addonPathData.'/flock.php',0666);
		
			if (flock($fp, LOCK_EX)) { // do an exclusive lock
			//This is now handeld as a critical section
			  $news  = array();
			 if (file_exists($addonPathData.'/news.php')) {
  			   include $addonPathData.'/news.php';
			 }	
             if (count($news) > 3) {
			   array_pop($news);
			 }
			 foreach ($post_data->commits as $commit) {
			   array_unshift($news,array('newsid' => $commit->id,
			                   'comment' => $commit->message));
			 }  
			 gpFiles::SaveArray($addonPathData.'/news.php','news',$news);
			 //Exit critical section
			 flock($fp, LOCK_UN); // release the lock
			 
			 exit(); //we can stop everything, no need to let server get unwanted data back
			} 
		}	
  }	   
  return $path;
}
?>
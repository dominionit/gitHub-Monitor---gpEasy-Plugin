<?php
/*
Developed by : Johannes Pretorius
Website : www.dominion-it.co.za/development/
Date : 24 Oct 2012
Dedicated to : My son who is very sick and has to go for surgery to remove cyst from his spinal cord.

*/
function gitHubArray($post_data){
  $result = array();
  
  $result['general']['forced'] = $post_data->forced; //was commit forced (usually true with a newly created repo)
  $result['general']['after'] = $post_data->after;
  $result['general']['before'] = $post_data->before;
  $result['general']['deleted'] = $post_data->deleted;
  $result['general']['ref'] = $post_data->ref; 
  $result['general']['compare'] = $post_data->compare; //compare url
  $result['general']['created'] = $post_data->created;  //was repo created with this post
  
  
  $result['pusher']['name'] = $post_data->pusher->name;
  $result['pusher']['email'] = $post_data->pusher->email;
  
  $result['repository']['name'] = $post_data->repository->name;
  $result['repository']['size'] = $post_data->repository->size;
  $result['repository']['has_wiki'] = $post_data->repository->has_wiki;
  $result['repository']['created_at'] = $post_data->repository->created_at;
  $result['repository']['private'] = $post_data->repository->private;
  $result['repository']['watchers'] = $post_data->repository->watchers;
  $result['repository']['url'] = $post_data->repository->url;
  $result['repository']['fork'] = $post_data->repository->fork;
  $result['repository']['language'] = $post_data->repository->language;
  $result['repository']['id'] = $post_data->repository->id;
  $result['repository']['pushed_at'] = $post_data->repository->pushed_at;
  $result['repository']['has_downloads'] = $post_data->repository->has_downloads;
  $result['repository']['open_issues'] = $post_data->repository->open_issues;
  $result['repository']['has_issues'] = $post_data->repository->has_issues;
  $result['repository']['stargazers'] = $post_data->repository->stargazers;
  $result['repository']['description'] = $post_data->repository->description;
  $result['repository']['forks'] = $post_data->repository->forks;
  $result['repository']['owner']['name'] = $post_data->repository->owner->name;
  $result['repository']['owner']['email'] = $post_data->repository->owner->email;
  
  foreach ($post_data->head_commit->modified as $targetFile) {
    $result['head_commit']['modified'][] = $targetFile;
  }	
  foreach ($post_data->head_commit->added as $targetFile) {
    $result['head_commit']['added'][] = $targetFile;
  }	
  foreach ($post_data->head_commit->removed as $targetFile) {
    $result['head_commit']['removed'][] = $targetFile;
  }	
  $result['head_commit']['author']['name'] = $post_data->head_commit->author->name;
  $result['head_commit']['author']['username'] = $post_data->head_commit->author->username;
  $result['head_commit']['author']['email'] = $post_data->head_commit->author->email;
  $result['head_commit']['committer']['name'] = $post_data->head_commit->committer->name;
  $result['head_commit']['committer']['username'] = $post_data->head_commit->committer->username;
  $result['head_commit']['committer']['email'] = $post_data->head_commit->committer->email;
  $result['head_commit']['timestamp'] = $post_data->head_commit->timestamp;
  $result['head_commit']['url'] = $post_data->head_commit->url;
  $result['head_commit']['id'] = $post_data->head_commit->id;
  $result['head_commit']['distinct'] = $post_data->head_commit->distinct;
  $result['head_commit']['message'] = $post_data->head_commit->message;
  $result['head_commit']['message'] = $post_data->head_commit->message;
  
  $counter = 0;
  foreach ($post_data->commits as $targetCommit) {
    $result['commits'][$counter] = array(
						'author' => array ( 
								'name' => $targetCommit->author->name,
								'username' => $targetCommit->author->username,
								'email' => $targetCommit->author->email
								),
						 'committer' => array ( 
								'name' => $targetCommit->committer->name,
								'username' => $targetCommit->committer->username,
								'email' => $targetCommit->committer->email
								),	
						  'message' =>	$targetCommit->message,		
						  'distinct' =>	$targetCommit->distinct,
						  'id' =>	$targetCommit->id,
						  'url' =>	$targetCommit->url,
						  'timestamp' =>	$targetCommit->timestamp,
						  'removed' => array(), //
						  'added' => array(),
						  'modified' => array(),
						);
	foreach ($targetCommit->removed as $targetFiles) {
			$result['commits'][$counter]['removed'] = $targetFiles;
	}							
	foreach ($targetCommit->modified as $targetFiles) {
			$result['commits'][$counter]['modified'] = $targetFiles;
	}							
	foreach ($targetCommit->added as $targetFiles) {
			$result['commits'][$counter]['added'] = $targetFiles;
	}							
	$counter++;
  }	
  return $result; 
}

function github_handle_post($path){
   if (( $_SERVER['REMOTE_ADDR'] == '207.97.227.253') || 
       ( $_SERVER['REMOTE_ADDR'] == '50.57.128.197') ||
	   ( $_SERVER['REMOTE_ADDR'] == '108.171.174.178') ||
	   ( $_SERVER['REMOTE_ADDR'] == '127.0.0.1')) {
	    //okay a post from gitHub to us
		if (isset($_POST['payload'])) {
			$post_data = json_decode($_POST['payload']);
			$gitHubData = gitHubArray($post_data);
			 $repository = $gitHubData['repository'];
			 $general = $gitHubData['general'];
			 $head_commit = $gitHubData['head_commit'];
			 $commits = $gitHubData['commits'];
			 $pusher = $gitHubData['pusher'];
			
			//error_log(print_r($post_data,true));
			$repo_name = $repository['name'];

			global $addonPathData;

			$oldmask = umask(0);//0755 //hardarse servers, cant get file rights correct for directory creation no mattter what.
			gpFiles::SaveArray($addonPathData.'/'.$repo_name.'/dummy.php','blank',$gitHubData );
			
			$fp = fopen($addonPathData.'/flock.php', "c");
			chmod($addonPathData.'/flock.php',0666);
		
			if (flock($fp, LOCK_EX)) { // do an exclusive lock
			//This is now handeld as a critical section
			
				//save latest repo info.
				gpFiles::SaveArray($addonPathData.'/'.$repo_name.'/latest.php','repository',$repository);
				
				if ($general['created'] == true) { 
				  //this can be a blank post and still carry the masters id, this first check, there is not suppose to be
				  //a file for it. if there is, exit and wait for new post with valid id. otherwise we override data.
				  if (file_exists($addonPathData.'/'.$repo_name.'/'.$head_commit['id'])) {
					//k wait till next push
					flock($fp, LOCK_UN); // release the lock
					umask($oldmask);
					exit();
				  }
				}
				
				//Index file for this repo needs to be updated.
				
				if (file_exists($addonPathData.'/'.$repo_name.'/repo_index.php')) {
				  $repo_index = array();
				  include $addonPathData.'/'.$repo_name.'/repo_index.php';
				}
				foreach ($commits as $commitItem) {
				  $repo_index[$result['general']['after']]['commits'][] = $commitItem['id'];
				  $repo_index[$result['general']['after']]['prev_commit'] = $result['general']['before'];
				}  
				gpFiles::SaveArray($addonPathData.'/'.$repo_name.'/repo_index.php','repo_index',$repo_index);
				
				//save the commits now
				gpFiles::SaveArray($addonPathData.'/'.$repo_name.'/'.$head_commit['id'],'commits',$commits,'general',$general,'head_commit',$head_commit,'pusher',$pusher);
				//unmask($oldmask);
			
				  $news  = array();
				 if (file_exists($addonPathData.'/news.php')) {
				   include $addonPathData.'/news.php';
				 }	
				 if (count($news) > 10) {
				   array_pop($news);
				 }
				   array_unshift($news,array(
										'newsid' => $head_commit['id'],
										'comment' => $head_commit['message'],
										'committer' => $head_commit['committer']['name'],
										'num_added' => count($head_commit['added']),
										'num_removed' => count($head_commit['removed']),
										'num_modified' => count($head_commit['modified']),
									)
								);
				 gpFiles::SaveArray($addonPathData.'/news.php','news',$news);
				 
			 //Exit critical section
			 flock($fp, LOCK_UN); // release the lock
			 umask($oldmask);
			 exit(); //we can stop everything, no need to let server get unwanted data back
			} 
		}	
  }	   
  return $path;
}
?>
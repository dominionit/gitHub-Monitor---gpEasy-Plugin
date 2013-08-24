<?php

function gitHub_prep_header(){
  global $addonFolderName,$page;
  $page->css_user[] =   '/data/_addoncode/'.$addonFolderName.'/css/github.css';
}

?>
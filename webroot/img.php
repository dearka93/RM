<?php


include(__DIR__.'/config.php');

$data =  array(
  'imgDir' => __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR ,
  'cacheDir' => __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR ,
  'maxWidth' => 2000,
  'maxHeight' => 2000,
  );

$pic = new CImage($data);

if(isset($_GET['src']))
    { $pic->sendSrc($_GET['src']); }

if(isset($_GET['save-as']))     
    { $pic->sendSaveAs($_GET['save-as']); }

if(isset($_GET['quality']))     
    { $pic->sendQuality($_GET['quality']); }

if(isset($_GET['no-cache']))    
    { $pic->sendNoCache(true); }

if(isset($_GET['width']))       
    { $pic->sendNewWidth($_GET['width']); }

if(isset($_GET['height']))      
    { $pic->sendNewHeight($_GET['height']); }

if(isset($_GET['crop-to-fit'])) 
    { $pic->sendCropToFit(true); }

if(isset($_GET['sharpen']))     
    { $pic->sendSharpen(true); }

if(isset($_GET['verbose']))     
    { $pic->sendVerbose(true); }

// Visa bilden
$pic->showImg();

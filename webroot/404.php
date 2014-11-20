<?php 
include(__DIR__.'/config.php'); 




$prot['title'] = "404";

$prot['main'] =<<<EOD
<h1>404 - Sidan finns inte</h1>     
<h2>Woops!<h2>
<img src="img/404/3.gif" alt="gif">
<img src="img/404/1.gif" alt="gif"> 
<img src="img/404/4.gif" alt="gif">
<img src="img/404/2.gif" alt="gif">
    
    
    
    
    
    
    
EOD;



include(PROT_THEME_PATH);


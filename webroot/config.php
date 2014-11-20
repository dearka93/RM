<?php

//raportera error
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly


//path 
define('PROT_INSTALL_PATH', __DIR__ . '/..');
define('PROT_THEME_PATH', PROT_INSTALL_PATH . '/theme/render.php');


//bootstrapping
include(PROT_INSTALL_PATH . '/src/bootstrap.php');

session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();

$prot = array();
/**
 * Settings for the database.
 *
 */

// för localhost
$prot['database']['dsn']            = 'mysql:host=localhost;dbname=Movie;';
$prot['database']['username']       = 'root';
$prot['database']['password']       = 'root';
$prot['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");

/*
$prot['database']['dsn']            = 'mysql:host=blu-ray.student.bth.se;dbname=guhu14;';
$prot['database']['username']       = 'guhu14';
$prot['database']['password']       = '5v38d7J=';
$prot['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"); 
*/
/**
 * Site wide settings.
 *
 */

$anv = new CUser(new CDatabase($prot['database']));
$prot['lang']         = 'sv';
$prot['title_append'] = ' | Protein Rental Movies';

$prot['header'] = <<<EOD
<img class='sitelogo' src='img/prot.jpg' alt='Prot Logo'/>
<span class='sitetitle'>Protein Rental Movies</span>
<span class='siteslogan'>Watch some Movies and drink some Protein </span>
<div class='right'>
<form action="movie_view.php" method="get">
  <p><label>Filmsök: <input type='search' name='title'/></label><input type='submit' name='search' value='Sök'/></p>
</form> 
</div>

EOD;
if($anv->IsAuthenticated()){
    $output = "Du är inloggad som: {$anv->GetAcronym()}"; 
}
else {
    $output = "Du är ej inloggad, logga in <a href='login.php'>här</a>";

}
$prot['footer'] = <<<EOD
<footer>
<span class='sitefooter'>
Copyright (c) Guanglei Huang (guanglei.huang@gmail.com) | <a href='https://github.com/dearka93/Protein'>Protein på GitHub</a> | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a> | $output
</span>

</footer>
EOD;


$prot['stylesheets'] = array('css/style.css','css/dice.css','css/navbar.css','css/figure.css', 'css/gallery.css', 'css/breadcrumb.css', 'css/forms.css');
$prot['favicon']    = 'favicon.ico';

$prot['navbar'] = array(
  'class' => 'nb-plain',
  'items' => array(
    'hem'         => array('text'=>'Start',         'url'=>'index.php', 'title' => 'Startsidan'),
   
    'movie'      => array('text'=>'Filmer', 'url'=>'movie_view.php', 'title' => 'Alla Filmer', 
    //sub ))
        'submenu' => array(
            'items' => array( 

            'item 1' => array('text'=>'Uppdatera Filmer', 'url'=>'movie_view_edit.php', 'title' => 'Updatera Film Information'),
            'item 2' => array('text'=>'Skapa', 'url'=>'movie_create.php', 'title' => 'Lägga till en Film'), 
            'item 3' => array('text'=>'Radera', 'url'=>'movie_view_delete.php', 'title' => 'Radera en Film'),            
        ), ), ),
      
          'content'     => array('text'=>'Nyheter',     'url'=>'view.php',      'title' => 'Se innehåll',
                                 
         'submenu' => array(
             'items' => array(
     'item 1'      => array('text'=>'Rea', 'url'=>'view.php?rea', 'title' => 'Rea produkter'),
     'item 2'      => array('text'=>'Nyheter från Protein RM', 'url'=>'view.php?nyheter', 'title' => 'Allt om Protein RM!'),
     'item 3'      => array('text'=>'Lägga till', 'url'=>'create.php', 'title' => 'Skapa ett Inlägg'),
     ), ), ), 
    'dice' => array('text'=>'Tävling', 'url'=>'dice.php', 'title' => 'Tärningsspelet 100'),
            
    'about'     => array('text'=>'Om oss',     'url'=>'about.php', 'title' => 'Om Protein RM'), 
    'login' => array('text'=>'Mitt Konto', 'url'=>'login.php', 'title' => 'Inloggning',
     //sub ))
        'submenu' => array(
            'items' => array(
    'item 1' => array('text'=>'Återställa FilmDB', 'url'=>'movie_reset.php', 'title' => 'Återställa Film Databas'),
    'item 2' => array('text'=>'Återställa NyheterDB', 'url'=>'content_reset.php', 'title' => 'Återställa Blogg Databas'), 
    'item 3' => array('text'=>'Registrera dig', 'url'=>'register.php', 'title' => 'Skapa ett nytt användarkonto'),
    'item 4' => array('text'=>'Alla användare', 'url'=>'users.php', 'title' => 'Lista ut alla användare'),
    'item 5' => array('text'=>'Logga ut', 'url'=>'logout.php', 'title' => 'Logga ut'),
  ), ), ), 
      
  ),
  
  'callback' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);


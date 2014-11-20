<?php


class CContent {
    

    protected $db;
    public $output = null;

    
    
    public function __construct($db) {
        $this->db = $db;
    }

    
    //visa content för vald ID
    public function getOneContent($id) {
        $sql = 'SELECT * FROM Content WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {            
  $c = $res[0];    
$url             = htmlentities($c->url, null, 'UTF-8');
$type         = htmlentities($c->type, null, 'UTF-8');
$published = htmlentities($c->published, null, 'UTF-8');
$filter     = htmlentities($c->filter, null, 'UTF-8');
$title         = htmlentities($c->title, null, 'UTF-8');
$data         = htmlentities($c->data, null, 'UTF-8');
$slug         = htmlentities($c->slug, null,'UTF-8'); 
 

}
else {
  die('Misslyckades: det finns inget innehåll med sådant id.');
}
        return $c;
    }
    
    
 
    public function updateContent() {
        // Get parameters & rensa bort skadlig kod
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$url    = isset($_POST['url'])   ? strip_tags($_POST['url']) : null;
$type   = isset($_POST['type'])  ? strip_tags($_POST['type']) : array();
$title  = isset($_POST['title']) ? $_POST['title'] : null;
$data   = isset($_POST['data'])  ? $_POST['data'] : array();
$slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
$filter = isset($_POST['filter']) ? $_POST['filter'] : array();
$published = isset($_POST['published'])  ? strip_tags($_POST['published']) : array();

          $sql = '
            UPDATE Content SET
              title   = ?,
              slug    = ?,
              url     = ?,
              data    = ?,
              type    = ?,
              filter  = ?,
              published = ?,
              updated = NOW()
            WHERE 
              id = ?
          ';
        $url = empty($url) ? null : $url;
        $params = array($title, $slug, $url, $data, $type, $filter, $published, $id);
        $res = $this->db->ExecuteQuery($sql, $params);
          
        if($res) {
            $output = 'Informationen sparades.';
        }
        else {
            $output = 'Informationen sparades EJ.';
        }
          return $output;
    }
       
    
    //länkten till content beror på Type/ för getallContent()
    public function getUrlToContent($content) {
  switch($content->type) {
    case 'news': return "news.php?url={$content->url}"; break;
    case 'sale': return "sale.php?slug={$content->slug}"; break;
    default: return null; break;
  }
}
    
    //för view.php få allt content med länkar
    public function getAllContent() {
    $sql = "SELECT *, (published <= NOW()) AS available FROM Content WHERE deleted IS NULL";
    $res = $this->db->executeSelectQueryAndFetchAll($sql);
    $items = null;
    foreach($res AS $key => $val) {
      $items .= "<li>" . htmlentities($val->type) . "(" .
      (!$val->available ? 'inte ' : null) . "publicerad): " . htmlentities($val->title, null, 'UTF-8') . " (
      <a href='edit.php?id={$val->id}'>editera</a>
      <a href='" . $this->getUrlToContent($val) . "'>visa</a>
      <a href='delete.php?id={$val->id}'>ta bort</a>)</li>\n";
    }

    return $items;
  }
    
    public function updateContentForm($c, $output) {
        // Sanitize content before using it.
        $id     = htmlentities($c->id, null, 'UTF-8');
        $title  = htmlentities($c->title, null, 'UTF-8');
        $slug   = htmlentities($c->slug, null, 'UTF-8');
        $url    = htmlentities($c->url, null, 'UTF-8');
        $data   = htmlentities($c->data, null, 'UTF-8');
        $type   = htmlentities($c->type, null, 'UTF-8');
        $filter = htmlentities($c->filter, null, 'UTF-8');
        $published = htmlentities($c->published, null, 'UTF-8');
        
        
        $form = "<form method=post>";
                $form .= "<fieldset>";
                $form .= "<legend>Uppdatera innehåll</legend>";
                $form .= "<output>" . $output . "</output>";
                $form .= "<input type='hidden' name='id' value='" . $id . "'/>";
                $form .= "<p><label>Titel:<br/><input type='text' name='title' value='" . $title . "'/></label></p>";
                $form .= "<p><label>Slug:<br/><input type='text' name='slug' value='" . $slug . "'/></label></p>";
                $form .= "<p><label>Url:<br/><input type='text' name='url' value='" . $url . "'/></label></p>";
                $form .= "<p><label>Text:<br/><textarea name='data'>" . $data . "</textarea></label></p>";
                $form .= "<p><label>Type:<br/><select name='type'>";
                $form .= "<option value='news'>News</option>";
                $form .= "<option value='sale'>Sale</option>";
                $form .= "</select></p>";
        if(isset($filter)){
                $form .= "<p><label>Filter: (Får ej vara tom!)<br/><input type='text' name='filter' value='" . $filter . "'/></label></p>";}
        else{
                $form .= "<p><label>Filter:<br/><input type='text' name='filter' value='markdown'/></label></p>";
        }
                $form .="
  <p><label>Publiceringsdatum:<br/><input type='text' name='published' value='{$published}' placeholder='YYYY-MM-DD' style='width:200px;'/></label></p>";
                $form .= "<p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>";
                $form .= "<p><a href='view.php'>Tillbaka</a>";
                $form .= "  |  <a href='delete.php?id=" . $id . "&title=" . $title . "'>Ta bort sida</a></p>";
            $form .= "</fieldset>";
        $form .= "</form>";
        
        return $form;
    } 
    
    
    //input form för creat.php
    public function createContent() {
        $form = "<form method=post>";
                $form .= "<fieldset>";
                $form .= "<legend>Skapa nytt innehåll</legend>";
                $form .= "<p><label>Titel:<br/><input type='text' name='title' value='' placeholder='Obligatorisk'/></label></p>";
                $form .= "<p><label>Slug:<br/><input type='text' name='slug' value='' placeholder='Valfritt'/></label></p>";
                $form .= "<p><label>Url:<br/><input type='text' name='url' value='' placeholder='Valfritt för Post'/></label></p>";
                $form .= "<p><label>Text:<br/><textarea name='data' placeholder='Valfritt'></textarea></label></p>";
                $form .= "<p><label>Type:<br/><select name='type'>";
                $form .= "<option value='news'>Nyheter</option>";
                $form .= "<option value='sale'>Rea</option>";
                $form .= "</select></p>";
                $form .= "<p><label>Filter: (Får ej vara tom!)<br/>";
                $form .="<input type=checkbox name='filter[]' value='bbcode' checked>bbcode
                <input type=checkbox name='filter[]' value='link'>link
                <input type=checkbox name='filter[]' value='markdown'>markdown
                <input type=checkbox name='filter[]' value='nl2br'>nl2br";
                $form .= "<p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>";
                $form .= "<p><a href='view.php'>Tillbaka</a></p>";
            $form .= "</fieldset>";
        $form .= "</form>";
        
        return $form;
    }
    
    
    
    public function saveNewContent() {
        $title  = isset($_POST['title']) ? $_POST['title'] : null;
        $slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
        $url    = isset($_POST['url'])   ? strip_tags($_POST['url']) : null;
        $data   = isset($_POST['data'])  ? $_POST['data'] : array();
        $type   = isset($_POST['type'])  ? strip_tags($_POST['type']) : array();
        $array = isset($_POST['filter']) ? $_POST['filter'] : array();
        $fltr = null;
         foreach($array AS $fil){
             $fltr .= $fil;
             $fltr .= ',';
         }
        $filter = $fltr{strlen($fltr)-1} == ',' ? substr($fltr, 0, -1) : $fltr;
//tar bort sista , 
            //slugify
            $s = strip_tags($title);
            $slugify = $this->slugify($s);

        $sql = '
            INSERT INTO Content (slug, url, type, title, data, filter, published, created) VALUES
            (?, ?, ?, ?, ?, ?, NOW(), NOW())
          '; 
        $url = empty($url) ? null : $url;
        $slug = empty($slug) ? $slugify : $slug;

        $params = array($slug, $url, $type, $title, $data, $filter);
        $res = $this->db->ExecuteQuery($sql, $params); 
          
        if($res) {
            header('Location: view.php');
        }
        else {
            die('Informationen sparades ej.');
        }
    }
    
        //ta bort content
        public function deleteContent($id) {
        $sql = '
            DELETE FROM Content
            WHERE 
              id = ? LIMIT 1
          ';
          
        $res = $this->db->ExecuteQuery($sql, array($id));
    
        if($res)    {
             header('Location: view.php?delete');
}
        else {
            $output = 'Sidan kunde ej raderas.';
        }
    return $output;   
    }   
    
    
    public function resetDB() {
        $sql = file_get_contents('content.sql');
        $res = $this->db->ExecuteQuery($sql);

        if($res) {
             header('Location: view.php?reset');
        }
        else {
            $output = 'Databasen kunde EJ återställas.';
        }
        
        return $output;
    }

    //debug
    
    public function Dump() {
        $debug= $this->db->Dump();
        return $debug;
  }
    
/**
 * Create a slug of a string, to be used as url.
 *
 * @param string $str the string to format as slug.
 * @returns str the formatted slug. 
 */
public function slugify($str) {
  $str = mb_strtolower(trim($str));
  $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
  $str = preg_replace('/[^a-z0-9-]/', '-', $str);
  $str = trim(preg_replace('/-+/', '-', $str), '-');
  return $str;
}
    public function recentPosts($antal){
        $sql = "
        SELECT * FROM Content ORDER BY id DESC LIMIT {$antal}"; 
        
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $html = null;
    $link = null;
    $html .= "<table style='width:450px;'>";
 foreach ($res as $key => $val)
 {  
    $type = $val->type;
    if($type='sale'){
        $link = 'sale.php?slug=';
        $link .= $val->slug;
        
    }
    else if($type='news'){
        $link = 'news.php?url=';
        $link .= $val->url;
        
    }  
    $title =  htmlentities($val->title, null, 'UTF-8');     
    $data =  htmlentities($val->data, null, 'UTF-8');
    
   
    $html .= "<tr><td><a href={$link} title='{$title}' style='font-weight:bold;'>$title</a><br/><span style='color:#ccc;'>[{$val->published}]</span><br/> $data ... <a href={$link} title='{$title}'> Läs mer &#187; </a></td></tr>";
 }
 
    $html .= "</table>";
        return $html;
  
    
    }
    
    
    
} 
<?php

class CUser
{
    private $db  = null;  
    private $acronym = null; 
    public $username = null;
    public $name = null;
    public $id = null;
    private $image = null;
    private $type = null;
   
    public function __construct($database)
    {
        $this->db = $database; 
    }
    public function IsAuthenticated()
    {
        $this->acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if($this->acronym)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
        public function Login($user, $password)
        {
            $sql = "SELECT acronym, name, id, type FROM User WHERE acronym = ? AND password = md5(concat(?, salt))";
    
        $params = array($user, $password);
        $paramsPrint = htmlentities(print_r($params, 1));
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
        if(isset($res[0]))
        {
            $_SESSION['user'] = $res[0]; 
        }
        }
    
        public function profileInfo($id) {
            $this->id = $id;
            $sql = 'SELECT * FROM User WHERE id = ?';
            $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($this->id));
            if(isset($res[0])) {
          $c = $res[0];
          $this->type    = htmlentities($c->type, null, 'UTF-8');
          $this->image   = htmlentities($c->image, null, 'UTF-8');
          $this->username = htmlentities($c->acronym, null, 'UTF-8');
          $this->name    = htmlentities($c->name, null, 'UTF-8');

      } else {
          header("Location: login.php?adm");
      }
  }
    
        public function Loggaut()
        {
            unset($_SESSION['user']);
        }
        public function GetAcronym()
        {
            return $this->acronym; 
        }
        public function GetName() 
        {
            return $_SESSION['user']->name; 
        }
        public function GetID()
        {
            return $_SESSION['user']->id;
        }

        public function adminCheck(){   
    $check = null; 
        if($this->IsAuthenticated()) {
        if($_SESSION['user']->type === 'admin'){
        $check = true;      
      }
      else {
        $check = false;
      }
    }
    return $check;      
    } 
    
    
        public function AuthenticationCheck()
        {
            $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
            
            isset($acronym)&&$this->adminCheck() or die(header('Location: logout.php?adm'));
        }
    
        public function register($acronym, $name, $password, $rePassword){
            if($password !== $rePassword) {
        return false;    
      }
            
      $sql = "SELECT acronym FROM User;";
      $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
      $users = null;
      foreach($res AS $val) {
        $user[] = $val->acronym;    
      }
      if(in_array($acronym, $user)) {
        return false;
      }
      $sql = "INSERT INTO User (acronym, name, type, salt) VALUES 
    (?, ?, 'user', unix_timestamp());";
      $params = array($acronym, $name);
      $res = $this->db->ExecuteQuery($sql, $params);

      if($res) {
          $sql = "UPDATE User SET password = md5(concat(?, salt)) WHERE acronym = ?;";
          $params = array($password, $acronym);
          $res = $this->db->ExecuteQuery($sql, $params);
          if($res) {
            return true;    
          } 
            else {
                return false;    
          }
      }
            
    else {
        return false;    
            }

        }
    
        public function userProfile(){
        $form = null;
        $form .="<figure class='pic'>      
<img src='img.php?src={$this->image}&amp;width=400&amp;height=400&amp;sharpen' alt='{$this->name}'/></figure>"; 
        $form .= "<form method='post'>
                    <fieldset>
                        <legend>Profile</legend>
                        <p><label>Namn: <input type='text' name='name' value='{$this->name}'></label></p>
                        <p><label>Användarnamn: <input type='text' name='acronym' value='{$this->username}'></label></p>
                        <p><label>Profilbild: <input type='text' name='image' value='{$this->image}'></label></p>";
        $form .= $this->adminCheck() ? "<p><label>Användargrupp:</label><br/>
                <input type='radio' name='type' value='admin' " . ($this->type=="admin" ? "checked" : null) . "> Admin
                <br><input type='radio' name='type' value='user' " . ($this->type=="user" ? "checked" : null) . "> User</p>\n" : null;
                        
            $form .="<input type='submit' value='Spara' name='submit'/> 
                        <input type='reset' value='Återställ'/>";
                        
            $form .="</fieldset>
                </form>";
            
      isset($_POST['submit'])  ? $this->saveProfile() : null;
            $output = null;
            $form .= $output;
              
            return $form;
        }
    
        public function saveProfile(){
       //Parameters
      $id = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : $this->id);
      $acronym = isset($_POST['acronym']) ? $_POST['acronym'] : null;
      $name = isset($_POST['name'])  ? $_POST['name']  : null;
      $image = isset($_POST['image'])  ? $_POST['image']  : null;
      $type = isset($_POST['type'])  ? $_POST['type']  : 'user';
                
            $sql = '
    UPDATE User SET
      acronym = ?,
      name = ?,
      image = ?,
        type = ?
    WHERE
      id = ?
    ';
      $params = array($acronym, $name, $image, $type, $id);
      $res = $this->db->ExecuteQuery($sql, $params);
            if($res) {
           header("Location:profil.php?id= " .$id );
          $output = "<output style='color:#ffa167;'>Informationen sparades!</output>";
      }
      else {
          $output = "<output style='color:#ffa167;'>Informationen sparades EJ!</output>";
      }
  }
    
    public function getParameters(){
// Get parameters
$this->hits  = isset($_GET['hits']) ? $_GET['hits'] : 8;
$this->page  = isset($_GET['page']) ? $_GET['page'] : 1;
$this->orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
$this->order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
// Check that incoming is valid
is_numeric($this->hits) or die('Check: Hits must be numeric.');
is_numeric($this->page) or die('Check: Page must be numeric.');
in_array($this->orderby, array('id', 'acronym', 'name','type')) or die('Check: Not valid column.');
in_array($this->order, array('asc', 'desc')) or die('Check: Not valid sort order.');
$this->delete = isset($_POST['delete'])  ? true : false;
$this->id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null); 
}
    
    
    
public function usersDisplay(){

$sql = "SELECT COUNT(id) AS rows FROM User";
$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);


// Get maximal pages
$max = ceil($res[0]->rows / $this->hits);

$sql = "SELECT * FROM User ORDER BY $this->orderby $this->order LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);


// html table
$tr = "<tr><th>Id " . $this->db->orderby('id') . "</th><th>Bild</th><th>Username " . $this->db->orderby('acronym') . "</th><th>Namn " . $this->db->orderby('name') . "</th><th>Type " . $this->db->orderby('type') . "</th>";
$tr .= $this->adminCheck() ? "<th>Ändra</th><th>Radera</th></tr>" : "</tr>";
foreach($res AS $key => $val) {
  $tr .= "<tr><td>{$val->id}</td><td><img src='img.php?src={$val->image}&amp;width=200&amp;sharpen' alt='{$val->acronym}'/></td><td>{$val->acronym}</td><td>{$val->name}</td><td>{$val->type}</td>";
  
  $tr .=$this->adminCheck() ?"<td><a href='profil.php?id={$val->id}'>Editera</a></td><td><a href='user_delete.php?id={$val->id}'>Radera</a></td></tr>":"</tr>";

}

$hitsPerPage = $this->db->getHitsPerPage(array(2, 4, 8));
$navigatePage = $this->db->getPageNavigation($this->hits, $this->page, $max);

$form = null;   
$form .= "<div class='dbtable'>
  <div class='rows'>{$hitsPerPage}</div>
  <table>";
$form .= $tr;
$form .= "</table>";
$form .= "<div class='pages'>{$navigatePage}</div>
</div>";

    return $form;
}
    
        public function GetDeleteForm(){
    $sql = 'SELECT * FROM User WHERE id = ?';
$params = array($this->id);
$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);

if(isset($res[0])) {
  $user = $res[0];
}
else {
  die('Failed: There is no user with that id');
}
    
    $form = null;
    $form .= "<form method=post>
  <fieldset>
  <legend>Radera konto: {$user->acronym}</legend>
  <input type='hidden' name='id' value='{$this->id}'/>
  <p><input type='submit' name='delete' value='Radera konto'/></p>
  </fieldset>
</form>";
        return $form;
    }
    
        public function deleteUser(){

  $sql = 'DELETE FROM User WHERE id = ? LIMIT 1';
  $this->db->ExecuteQuery($sql, array($this->id));
 
  header('Location: users.php?deleted');
    
    
    }
    
    
    
}
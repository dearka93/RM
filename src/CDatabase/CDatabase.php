<?php

/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CDatabase {
 
  /**
   * Members
   */
  private $options;                   // Options used when creating the PDO object
  protected $db   = null;               // The PDO object
  private $stmt = null;               // The latest statement used to execute a query
  private static $numQueries = 0;     // Count all queries made
  private static $queries = array();  // Save all queries for debugging purpose
  private static $params = array();   // Save all parameters for debugging purpose


 /**
   * Constructor creating a PDO object connecting to a choosen database.
   *
   * @param array $options containing details for connecting to the database.
   *
   */
  public function __construct($options) {
    $default = array(
      'dsn' => null,
      'username' => null,
      'password' => null,
      'driver_options' => null,
      'fetch_style' => PDO::FETCH_OBJ,
    );
    $this->options = array_merge($default, $options);
 
    try {
      $this->db = new PDO($options['dsn'], $this->options['username'], $this->options['password'], $this->options['driver_options']);
    }
    catch(Exception $e) {
      //throw $e; // For debug purpose, shows all connection details
      throw new PDOException('Could not connect to database, hiding connection details.'); // Hide connection details.
    }
 
    $this->db->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->options['fetch_style']); 

    // Get debug information from session if any.
    if(isset($_SESSION['CDatabase'])) {
      self::$numQueries = $_SESSION['CDatabase']['numQueries'];
      self::$queries    = $_SESSION['CDatabase']['queries'];
      self::$params     = $_SESSION['CDatabase']['params'];
      unset($_SESSION['CDatabase']);
    }
  }


 /**
   * Execute a select-query with arguments and return the resultset.
   * 
   * @param string $query the SQL query with ?.
   * @param array $params array which contains the argument to replace ?.
   * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
   * @return array with resultset.
   */
  public function ExecuteSelectQueryAndFetchAll($query, $params=array(), $debug=false) {
 
    self::$queries[] = $query; 
    self::$params[]  = $params; 
    self::$numQueries++;
 
    if($debug) {
      echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
    }
 
    $this->stmt = $this->db->prepare($query);
    $this->stmt->execute($params);
    return $this->stmt->fetchAll();
  }
    
 /**
   * Execute a SQL-query and ignore the resultset.
   *
   * @param string $query the SQL query with ?.
   * @param array $params array which contains the argument to replace ?.
   * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
   * @return boolean returns TRUE on success or FALSE on failure. 
   */
  public function ExecuteQuery($query, $params = array(), $debug=false) {
 
    self::$queries[] = $query; 
    self::$params[]  = $params; 
    self::$numQueries++;
 
    if($debug) {
      echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
    }
 
    $this->stmt = $this->db->prepare($query);
    return $this->stmt->execute($params);
  }
    
 /**
   * Get a html representation of all queries made, for debugging and analysing purpose.
   * 
   * @return string with html.
   */
  public function Dump() {
    $html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';
    foreach(self::$queries as $key => $val) {
      $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1)) . '<br/></br>';
      $html .= $val . '<br/></br>' . $params;
    }
    return $html . '</pre>';
  }
    
  /**
   * Return last insert id.
   */
  public function LastInsertId() {
    return $this->db->lastInsertid();
  }
    
/**
   * Save debug information in session, useful as a flashmemory when redirecting to another page.
   * 
   * @param string $debug enables to save some extra debug information.
   */
  public function SaveDebug($debug=null) {
    if($debug) {
      self::$queries[] = $debug;
      self::$params[] = null;
    }
 
    self::$queries[] = 'Saved debuginformation to session.';
    self::$params[] = null;
 
    $_SESSION['CDatabase']['numQueries'] = self::$numQueries;
    $_SESSION['CDatabase']['queries']    = self::$queries;
    $_SESSION['CDatabase']['params']     = self::$params;
  }
    
  /**
   * Return rows affected of last INSERT, UPDATE, DELETE
   */
  public function RowCount() {
    return is_null($this->stmt) ? $this->stmt : $this->stmt->rowCount();
  }
    
    //återställa Databasen för Movie
    public function resetDB() {
        $sql = file_get_contents('movie.sql');
        $res = $this->ExecuteQuery($sql);

        if($res) {
            header('Location: movie_connect.php?reset');
        }
        else {
            $output = 'Databasen kunde ej återställas.';
        }
        
    }    
    
public function orderby($column) {
  return "<span class='orderby'><a href='?orderby={$column}&order=asc'>&darr;</a><a href='?orderby={$column}&order=desc'>&uarr;</a></span>";
}


public function getQueryString($options, $prepend='?') {
  // parse query string into array
  $query = array();
  parse_str($_SERVER['QUERY_STRING'], $query);

  // Modify the existing query string with new options
  $query = array_merge($query, $options);

  // Return the modified querystring
  return $prepend . http_build_query($query);
}


public function getHitsPerPage($hits) {
  $nav = "Träffar per sida: ";
  foreach($hits AS $val) {
    $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
  }  
  return $nav;
}


public function getPageNavigation($hits, $page, $max, $min=1) {
  $nav  = "<a href='" . $this->getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> ";
  $nav .= "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> ";

  for($i=$min; $i<=$max; $i++) {
    $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'>$i</a> ";
  }

  $nav .= "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> ";
  $nav .= "<a href='" . $this->getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> ";
  return $nav;
}



public function getParameters(){
// Get parameters
$this->hits  = isset($_GET['hits']) ? $_GET['hits'] : 8;
$this->page  = isset($_GET['page']) ? $_GET['page'] : 1;
$this->title = isset($_POST['title']) ? strip_tags($_POST['title']) : (isset($_GET['title']) ? $_GET['title'] : null);
$this->genre = isset($_GET['genre']) ? $_GET['genre'] : null;
$this->year1 = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
$this->year2 = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
$this->orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
$this->order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
// Check that incoming is valid
is_numeric($this->hits) or die('Check: Hits must be numeric.');
is_numeric($this->page) or die('Check: Page must be numeric.');
is_numeric($this->year1) || !isset($this->year1)  or die('Check: Year must be numeric or not set.');
is_numeric($this->year2) || !isset($this->year2)  or die('Check: Year must be numeric or not set.');
in_array($this->orderby, array('id', 'title', 'year','pris')) or die('Check: Not valid column.');
in_array($this->order, array('asc', 'desc')) or die('Check: Not valid sort order.');
$this->delete = isset($_POST['delete'])  ? true : false;
$this->id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null); 
$this->save   = isset($_POST['save'])  ? true : false;
$this->create = isset($_POST['create'])  ? true : false;


}
    
    


public function movieDisplay(){

$sql = '
  SELECT DISTINCT G.name
  FROM Genre AS G
    INNER JOIN Movie2Genre AS M2G
      ON G.id = M2G.idGenre
';
$res = $this->ExecuteSelectQueryAndFetchAll($sql);

$genres = null;
foreach($res as $val) {
  if($val->name == $this->genre) {
    $genres .= "$val->name ";
  }
  else {
    $genres .= "<a href='" . $this->getQueryString(array('genre' => $val->name)) . "'>{$val->name}</a> ";
  }
}

// Prepare the query based on incoming arguments
$sqlOrig = '
  SELECT 
    M.*, 
    GROUP_CONCAT(G.name) AS genre 
  FROM Movie AS M
    LEFT OUTER JOIN Movie2Genre AS M2G
      ON M.id = M2G.idMovie
    INNER JOIN Genre AS G
      ON M2G.idGenre = G.id
';
$where    = null;
$groupby  = ' GROUP BY M.id';
$limit    = null;
$sort     = " ORDER BY $this->orderby $this->order";
$params   = array();

// Select by title
if($this->title) {
  $where .= ' AND title LIKE ?';
  $params[] = $this->title;
} 

// Select by year
if($this->year1) {
  $where .= ' AND year >= ?';
  $params[] = $this->year1;
} 
if($this->year2) {
  $where .= ' AND year <= ?';
  $params[] = $this->year2;
} 

// Select by genre
if($this->genre) {
  $where .= ' AND G.name = ?';
  $params[] = $this->genre;
} 

// Pagination
if($this->hits && $this->page) {
  $limit = " LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
}

// Complete the sql statement
$where = $where ? " WHERE 1 {$where}" : null;
$sql = $sqlOrig . $where . $groupby . $sort . $limit;
$res = $this->ExecuteSelectQueryAndFetchAll($sql, $params);
    
// html table
$tr = "<tr><th>Rad</th><th>Id " . $this->orderby('id') . "</th><th>Bild</th><th>Titel " . $this->orderby('title') . "</th><th>År " . $this->orderby('year') . "</th><th>Genre</th><th>Pris " . $this->orderby('pris') . "</th></tr>";
foreach($res AS $key => $val) {
  $tr .= "<tr><td>{$key}</td><td>{$val->id}</td><td><a href='movie_info.php?id={$val->id}'><img src='img.php?src={$val->image}&amp;width=200&amp;sharpen' alt='{$val->title}'/></a></td><td>{$val->title}</td><td>{$val->year}</td><td>{$val->genre}</td><td>{$val->pris}</td></tr>";
}
// Get max pages for current query, for navigation
$sql = "
  SELECT
    COUNT(id) AS rows
  FROM 
  (
    $sqlOrig $where $groupby
  ) AS Movie
";
$res = $this->ExecuteSelectQueryAndFetchAll($sql, $params);
$rows = $res[0]->rows;
$max = ceil($rows / $this->hits);


$hitsPerPage = $this->getHitsPerPage(array(2, 4, 8));
$navigatePage = $this->getPageNavigation($this->hits, $this->page, $max);
    
$form = "<form>";
$form .= "<fieldset>";
$form .= "<legend>Sök</legend>";
$form .=   "<input type=hidden name=genre value='{$this->genre}'/>";
$form .=    "<input type=hidden name=hits value='{$this->hits}'/>";
$form .="<input type=hidden name=page value='1'/>";
$form .= "<p><label>Titel (delsträng, använd % som *): <input type='search' name='title' value='{$this->title}'/></label></p>";
$form .= "<p><label>Välj genre:</label> {$genres}</p>";
$form .= "<p><label>Skapad mellan åren: 
      <input type='text' name='year1' value='{$this->year1}'/></label>
      - 
      <label><input type='text' name='year2' value='{$this->year2}'/></label>
    
  </p>";
$form .=    "<p><input type='submit' name='submit' value='Sök'/></p>";
$form .=    "<p><a href='?'>Visa alla</a></p>";
$form .=    "</fieldset>";
$form .=  "</form>";

$form .=  "<div class='dbtable'>";
$form .=    "<div class='rows'>{$rows} träffar. {$hitsPerPage}</div>";
$form .=   "<table>";
$form .=    $tr;
$form .=    "</table>";
$form .=    "<div class='pages'>{$navigatePage}</div>";
$form .=  "</div>";
    
return $form;
}
    
public function movieEditDisplay(){

$sql = "SELECT COUNT(id) AS rows FROM VMovie";
$res = $this->ExecuteSelectQueryAndFetchAll($sql);


// Get maximal pages
$max = ceil($res[0]->rows / $this->hits);

$sql = "SELECT * FROM VMovie ORDER BY $this->orderby $this->order LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
$res = $this->ExecuteSelectQueryAndFetchAll($sql);


// html table
$tr = "<tr><th>Rad</th><th>Id " . $this->orderby('id') . "</th><th>Bild</th><th>Titel " . $this->orderby('title') . "</th><th>År " . $this->orderby('year') . "</th><th>Genre</th><th>Pris " . $this->orderby('pris') . "</th><th>Ändra</th></tr>";
foreach($res AS $key => $val) {
  $tr .= "<tr><td>{$key}</td><td>{$val->id}</td><td><a href='movie_info.php?id={$val->id}'><img src='img.php?src={$val->image}&amp;width=200&amp;sharpen' alt='{$val->title}'/></a></td><td>{$val->title}</td><td>{$val->year}</td><td>{$val->genre}</td><td>{$val->pris}</td><td><a href='movie_edit.php?id={$val->id}'>Editera</a></td></tr>";
}

$hitsPerPage = $this->getHitsPerPage(array(2, 4, 8));
$navigatePage = $this->getPageNavigation($this->hits, $this->page, $max);

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
  
    
    public function editMovie(){
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$title  = isset($_POST['title']) ? strip_tags($_POST['title']) : null;
$year   = isset($_POST['year'])  ? strip_tags($_POST['year'])  : null;
$image  = isset($_POST['image']) ? strip_tags($_POST['image']) : null;
$length  = isset($_POST['length']) ? strip_tags($_POST['length']) : null;
$director  = isset($_POST['director']) ? strip_tags($_POST['director']) : null;
$plot  = isset($_POST['plot']) ? strip_tags($_POST['plot']) : null;
$pris  = isset($_POST['pris']) ? strip_tags($_POST['pris']) : null;
$imdb  = isset($_POST['imdb']) ? strip_tags($_POST['imdb']) : null;
$youtube  = isset($_POST['youtube']) ? strip_tags($_POST['youtube']) : null;
$array = isset($_POST['genre']) ? $_POST['genre'] : array();
        $gen = null;
        $sqlGen = 'INSERT INTO Movie2Genre (idMovie, idGenre) VALUES
        (?,?)
';
        //
        $sqlGenRes = 'DELETE FROM Movie2Genre
        WHERE idMovie = ?
        ';
        $parmas1 = array($id);
        $this->ExecuteQuery($sqlGenRes, $parmas1);
        foreach($array AS $gen){
            $params = array($id,$gen);
            $this->ExecuteQuery($sqlGen, $params);
 
         }
        
        
  $sql = '
    UPDATE Movie SET
      title = ?,
      year = ?,
      length = ?,
      director = ?,
      plot = ?,
      pris = ?,
      imdb = ?,
      youtube = ?,
      image = ?
    WHERE 
      id = ?
  ';
  $params = array($title, $year, $length, $director, $plot, $pris, $imdb, $youtube, $image, $id);
  $this->ExecuteQuery($sql, $params);
  $output = 'Informationen sparades.';
        return $output;
    }

    public function getMovieInfo($id){
// Select information on the movie 
$sql = 'SELECT * FROM Vmovie WHERE id = ?';
$params = array($id);
$res = $this->ExecuteSelectQueryAndFetchAll($sql, $params);
 
if(isset($res[0])) {
    $movie = $res[0];
    $title = $movie->title;
    $year = $movie->year;
    $image = $movie->image;
    $id = $movie->id;
    $pris = $movie->pris;
    $plot = $movie->plot;
    $director = $movie->director;
    $length = $movie->length;
    $imdb = $movie->imdb;
    $youtube = $movie->youtube;
    $genre = $movie->genre;
}
else {
  die('Failed: There is no movie with that id');
}
 return $movie;

    }
    
    
        public function getEditMovie($movie, $output){
    $title = $movie->title;
    $year = $movie->year;
    $image = $movie->image;
    $id = $movie->id;
    
    $pris = $movie->pris;
    $plot = $movie->plot;
    $director = $movie->director;
    $length = $movie->length;
    $imdb = $movie->imdb;
    $youtube = $movie->youtube;
    $genre = $movie->genre;
    
    $pieces = explode(",", $genre);
    $check1 = null;
    $check2 = null;
    $check3 = null;
    $check4 = null;
    $check5 = null;
    $check6 = null;
    $check7 = null;
    $check8 = null;
    $check9 = null;
    $check10 = null;
    $check11 = null;
    $check12 = null;
    
    foreach($pieces AS $gen){
        switch ($gen) {
            case "comedy":
            $check1='checked';
            break;
            
            case "romance":
            $check2='checked';
            break;
            
            case "college":
            $check3='checked';
            break;
            
            case "crime":
            $check4='checked';
            break;
            
            case "drama":
            $check5='checked';
            break;
            
            case "thriller":
            $check6='checked';
            break;
            
            case "animation":
            $check7='checked';
            break;
            
            case "adventure":
            $check8='checked';
            break;
            
            case "family":
            $check9='checked';
            break;
            
            case "svenskt":
            $check10='checked';
            break;
            
            case "action":
            $check11='checked';
            break;
            
            case "horror":
            $check12='checked';
            break;
        }
 
         }  
    

            $form = null;
$form .="<form method=post>
  <fieldset>
  <legend>Uppdatera information om film nummer: {$id}</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><label>Titel:<br/><input type='text' name='title' value='{$title}'/></label></p>
  <p><label>År:<br/><input type='text' name='year' value='{$year}'/></label></p>
  <p><label>Längd:<br/><input type='text' name='length' value='{$length}'/></label></p>
  <p><label>Regissör:<br/><input type='text' name='director' value='{$director}'/></label></p>
  <p><label>Bild:<br/><input type='text' name='image' value='{$image}'/></label></p>
  <p><label>Plot:<br/><input type='text' name='plot' value='{$plot}'/></label></p>
  <p><label>Pris:<br/><input type='text' name='pris' value='{$pris}'/></label></p>  
  <p><label>IMDB:<br/><input type='text' name='imdb' value='{$imdb}'/></label></p>
  <p><label>Youtube:<br/><input type='text' name='youtube' value='{$youtube}'/></label></p>
  <p><label>Genre: (Får ej vara tom!)</br>
  <input type=checkbox name='genre[]' value='1'$check1/>comedy
  <input type=checkbox name='genre[]' value='2'$check2/>romance
  <input type=checkbox name='genre[]' value='3'$check3/>college
  <input type=checkbox name='genre[]' value='4'$check4/>crime
  <input type=checkbox name='genre[]' value='5'$check5/>drama
  <input type=checkbox name='genre[]' value='6'$check6/>thriller
  <input type=checkbox name='genre[]' value='7'$check7/>animation
  <input type=checkbox name='genre[]' value='8'$check8/>adventure
  <input type=checkbox name='genre[]' value='9'$check9/>family
  <input type=checkbox name='genre[]' value='10'$check10/>svenskt
  <input type=checkbox name='genre[]' value='11'$check11/>action
  <input type=checkbox name='genre[]' value='12'$check12/>horror
  </label></p>
  <p><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>
  <p><a href='movie_view_edit.php'>Visa alla</a></p>
  <output>{$output}</output>
  </fieldset>
</form>";        
        
return $form;
    
}
    
    public function createMovie(){

     $sql = 'INSERT INTO Movie (title) VALUES (?)';
  $this->ExecuteQuery($sql, array($this->title));
  $this->SaveDebug(); 
        
  header('Location: movie_edit.php?id=' . $this->LastInsertId());
        
  exit;
    
    
    }
    
    public function createFilmForm(){
    $form = null;
    $form .= "<form method=post>
  <fieldset>
  <legend>Skapa ny film</legend>
  <p><label>Titel:<br/><input type='text' name='title'/></label></p>
  <p><input type='submit' name='create' value='Skapa'/></p>
  </fieldset>
</form>";

    return $form;
    }
    
    
    public function GetDeleteForm(){
    $sql = 'SELECT * FROM Movie WHERE id = ?';
$params = array($this->id);
$res = $this->ExecuteSelectQueryAndFetchAll($sql, $params);

if(isset($res[0])) {
  $movie = $res[0];
}
else {
  die('Failed: There is no movie with that id');
}
    
    $form = null;
    $form .= "<form method=post>
  <fieldset>
  <legend>Radera film: {$movie->title}</legend>
  <input type='hidden' name='id' value='{$this->id}'/>
  <p><input type='submit' name='delete' value='Radera film'/></p>
  </fieldset>
</form>";
        return $form;
    }
    
    public function deleteMovie(){
     $sql = 'DELETE FROM Movie2Genre WHERE idMovie = ?';
  $this->ExecuteQuery($sql, array($this->id));
  $this->SaveDebug("Det raderades " . $this->RowCount() . " rader från databasen.");
 
  $sql = 'DELETE FROM Movie WHERE id = ? LIMIT 1';
  $this->ExecuteQuery($sql, array($this->id));
  $this->SaveDebug("Det raderades " . $this->RowCount() . " rader från databasen.");
 
  header('Location: movie_view_delete.php');
    
    
    }
   
    public function getDeleteView(){
        $sql = "SELECT COUNT(id) AS rows FROM VMovie";
$res = $this->ExecuteSelectQueryAndFetchAll($sql);

$max = ceil($res[0]->rows / $this->hits);

$sql = "SELECT * FROM VMovie ORDER BY $this->orderby $this->order LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
$res = $this->ExecuteSelectQueryAndFetchAll($sql);

        $hitsPerPage = $this->getHitsPerPage(array(2, 4, 8));
        $navigatePage = $this->getPageNavigation($this->hits, $this->page, $max);
        
// html table
$tr ="<div class='dbtable'>
  <div class='rows'>{$hitsPerPage}</div>
  <table>";
$tr .= "<tr><th>Rad</th><th>Id " . $this->orderby('id') . "</th><th>Bild</th><th>Titel " . $this->orderby('title') . "</th><th>År " . $this->orderby('year') . "</th><th>Pris " . $this->orderby('pris') . "</th><th>Radera</th></tr>";
foreach($res AS $key => $val) {
  $tr .= "<tr><td>{$key}</td><td>{$val->id}</td><td><a href='movie_info.php?id={$val->id}'><img src='img.php?src={$val->image}&amp;width=200&amp;sharpen' alt='{$val->title}'/></a></td><td>{$val->title}</td><td>{$val->year}</td><td>{$val->pris}</td><td><a href='movie_delete.php?id={$val->id}'>Ta bort</a></td></tr>";
}
    $tr .="</table>
  <div class='pages'>{$navigatePage}</div>
</div>";

        
        
        return $tr;
    }
    
    public function displayMovieInfo($movie){
    $form = null;
    $form .= "<figure class='pic'>      
<img src='img.php?src={$movie->image}&amp;width=330&amp;height=470&amp;sharpen' alt='{$movie->title}'/></figure>
        <div class = 'movInfo'>
        <h3>{$movie->title} ({$movie->year})<a href='{$movie->imdb}'><img src='img.php?src=imdb.png&amp;width=20' alt='imdb'/></a></h3>
        <hr>
        <p><b>Regissör: </b>{$movie->director}</p>
        <p><b>År: </b>{$movie->year}</p>
        <p><b>Längd: </b>{$movie->length}minuter</p>
        <p><b>Genre: </b>{$movie->genre}</p>
        <p>{$movie->plot}</p>
        <h3>Pris: </b>{$movie->pris}kr</p>
        <p>{$movie->youtube}</p>
        </div>
        ";
        return $form;
    }
    
    public function recentMovies($antal){
 $sql = "SELECT image, title, id FROM Vmovie ORDER BY id DESC LIMIT {$antal};";       
 $res = $this->ExecuteSelectQueryAndFetchAll($sql);
 
 $html = "<div>";
 foreach ($res as $key => $val)
 {
    $title = htmlentities($val->title, null, 'UTF-8');
    $html .= "
    <a href='movie_info.php?id={$val->id}' title='{$title}'>
    <img src='img.php?src={$val->image}&amp;width=400&amp;height=400' alt='{$title}' style='border: 2px solid #13e5e5; margin: 30px;'/></a>";   
}
 
    $html .= "</div>";
 return $html;
}
   
    public function activeMovieGenre(){
        $sql = "SELECT DISTINCT G.name
  FROM Genre AS G
    INNER JOIN Movie2Genre AS M2G
      ON G.id = M2G.idGenre;";
        $res = $this->ExecuteSelectQueryAndFetchAll($sql);
        $genreTable = null;
        foreach($res as $genre){
                $genreTable .= "
                    <a href='movie_view.php?genre={$genre->name}'>{$genre->name}</a>";
            }
        return $genreTable;
           
    }
    

    
}

?>
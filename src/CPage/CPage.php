<?php


class CPage extends CContent{
    //private $db;
   


/*    public function __construct($db) {
            $this->db = $db;
} */

    public function getPageContent($url){
        // Get content
        $urlSql = $url ? 'url = ?' : '1';

        $sql = "
        SELECT *
        FROM Content
        WHERE
        type = 'news' AND
        $urlSql AND
        published <= NOW();
        ";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));

        if(isset($res[0])) {
            $c = $res[0];
}
        else {
            die('Misslyckades: det finns inget innehåll.');
}
        return $c;
            
}

    public function getPageData($c){
        $filterklass = new CTextFilter();
        $res = $filterklass->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);
        return $res;
    
}

        public function getBlogContent($slug){
        // Get content
        $slugSql = $slug ? 'slug = ?' : '1';

        $sql = "
        SELECT *
        FROM Content
        WHERE
        type = 'sale' AND
        $slugSql AND
        published <= NOW();
        ";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));

        if(isset($res[0])) {
            $c = $res[0];
}
        else {
            die('Misslyckades: det finns inget innehåll.');
}
        return $c;
            
}


    

    public function breadCrumb($type, $link, $name){
        $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='view.php'>Nyheter</a> »</li>\n";
        if($type=='sale') {
          $breadcrumb .= "<li><a href='view.php?rea'>Erbjudande från Protein RM</a> » </li>\n";
          $breadcrumb .= "<li><a href='sale.php?slug={$link}'>{$name}</a> » </li>\n";
        }
        else if($type=='news') {
          $breadcrumb .= "<li><a href='view.php?nyheter'>Nyheter från Protein RM</a> » </li>\n";
          $breadcrumb .= "<li><a href='news.php?url={$link}'>{$name}</a> » </li>\n";
        }
        $breadcrumb .= "</ul>\n";
        return $breadcrumb;

    }
    





}
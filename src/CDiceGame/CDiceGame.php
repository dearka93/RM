<?php


class CDiceGame {
    

    private $dices;
    private $number;
    private $sum;
    private $sumCast;
    private $sumAll;
    public $kast;
 

    public function __construct($number = 1) {
        for($i=0; $i < $number; $i++) {
            $this->dices[] = new CDiceImage();
        }
        
        session_name('game');
        
        $this->number = $number;
        $this->sum = 0;
        $this->sumCast = 0;
        $this->sumAll = 0;
    }
    
    
    public function ToDo($roll, $save, $destroy) {
        if($roll == true) {
            $this->Roll();
        }
        else if($save == true) {
            $this->SaveCast();
        }
        else if($destroy == true) {
            $this->DestroySess();
        }
    }
    
    
    private function DestroySess() {
        // Unset all of the session variables.
          $_SESSION = array();

          // If it's desired to kill the session, also delete the session cookie.
          // Note: This will destroy the session, and not just the session data!
          if (ini_get("session.use_cookies")) {
              $params = session_get_cookie_params();
              setcookie(session_name(), '', time() - 42000,
                  $params["path"], $params["domain"],
                  $params["secure"], $params["httponly"]
              );
          }

          // Finally, destroy the session.
          session_destroy();
          $this->kast = 0;
          header('Location: dice.php');
    }
    
 //roll
    public function Roll() {
        $this->kast++;
        $this->sum = 0;
        for($i=0; $i < $this->number; $i++) {
            $roll = $this->dices[$i]->Roll(1);
            $this->sum += $roll;
            $this->sumCast += $roll;
              if( $this->sum == 1) { 
            $this->sumCast = 0;
        }
        }
    }
 
    public function countCheck(){
        if($this->kast>10){
        return true;
        
        }
        else return false;
    }
    
    public function castLeft(){
        $rest = 10-$this->kast;
        return $rest;
    
    }

    public function GetPoints() {
        $this->dices->GetTotal();
    }

  

    public function GetCastAll() {
        
        return $this->sumCast;
    }
 
 
    public function SaveCast() {
        $this->kast++;
        $this->sumAll += $this->sumCast;
        $this->sumCast = 0;
    } 

    public function GetSumAll() {
        if ($this->sumAll>20){
            $this->sumAll .= "<output style='color:#ea702d;'/>Grattis! Nu har du vunnit dagens film -></output>";
        
        }    
        return $this->sumAll;
    }
 
    

    public function GetRollsAsImageList() {
        $html = "<ul class='dice'>";
        foreach($this->dices as $dice) {
            $val = $dice->GetLastRoll();
            $html .= "<li class='dice-{$val}'></li>";
        }
        $html .= "</ul>";
        return $html;
    }
}
 
?>
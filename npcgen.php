<?php

class Backend {

   var $db;
   var $connArray;
 

   function __construct($input)
   {   
		// TODO mapping yap
	   $this->db =  new mysqli($input["hostname"], $input["username"], $input["password"], $input["database"]);
	   if ($this->db->connect_error) {
   		 die("Connection failed: " . $this->db->connect_error);
		}
		mysqli_set_charset($this->db,"utf8mb4_unicode_ci");
   }

   // Updates array in place and also returns array for convenience.
   function specialCharCleanup($Object){
	   foreach($Object as &$entry){
		  // echo $entry;
		  if (is_array($entry)){
			$entry = $this->specialCharCleanup($entry);
		  }else{
		   $entry =  utf8_encode($entry);
		  }
		  // echo $entry;
	   }
	   return $Object;
   }

 	function getList($tableName){
		$sql="SELECT ID,NAME FROM {$tableName}";
		$object;
		if ($result = $this->db->query($sql)) {
			for ($row_no = $result->num_rows - 1; $row_no >= 0; $row_no--) {
    			$result->data_seek($row_no);
				$row = $result->fetch_assoc();
				$object[$row['ID']] = $row;  				
			}
			$this->db->close();
		}
		 // var_dump($object);
		return $this->specialCharCleanup( $object);
		// return $object;
	}

	function getRace($id){
		//var_dump($id);
		$sql="SELECT r.ID, r.NAME, t1.NAME AS T1NAME, t1.CONTENT AS T1CONTENT, t2.NAME AS T2NAME, t2.CONTENT AS T2CONTENT, a.NAME AS ANAME, a.CONTENT AS ACONTENT, ab1.ABILITY AS AB1ABILITY, ab1.BONUS AS AB1BONUS, ab2.ABILITY AS AB2ABILITY, ab2.BONUS AS AB2BONUS FROM RACES AS r JOIN TRAITS AS t1 ON r.TRAIT1 = t1.ID JOIN TRAITS AS t2 ON r.TRAIT2 = t2.ID JOIN ACTIONS AS a ON r.ACTION = a.ID JOIN ABILITY_BONUSES AS ab1 ON r.ABILITY_BONUS1 = ab1.ID LEFT JOIN ABILITY_BONUSES AS ab2 ON r.ABILITY_BONUS2 = ab2.ID WHERE r.ID={$id}";
	 	$object=NULL;
		if ($result = $this->db->query($sql)) {
			$object = $result->fetch_assoc();
			$this->db->close();
			//echo "called";			
		}
		var_dump( utf8_encode(implode(',',$object)));
		return $this->specialCharCleanup( $object);
		//return $object;
	}

	function getClass($id){
		//var_dump($id);
		$sql="SELECT r.ID, r.NAME, r.STAT1, r.STAT2, r.STAT3, r.STAT4, r.STAT5, r.STAT6, a1.NAME AS A1NAME, a1.CONTENT AS A1CONTENT, a2.NAME AS A2NAME, a2.CONTENT AS A2CONTENT, dd.COUNT AS DCOUNT, dd.DIE AS DDIE FROM CLASSES AS r JOIN ACTIONS AS a1 ON r.ACTION1 = a1.ID JOIN ACTIONS AS a2 ON r.ACTION2 = a2.ID JOIN DMG_DICE as dd ON r.HD = dd.ID  WHERE r.ID={$id}";
	 	$object=NULL;
		if ($result = $this->db->query($sql)) {
			$object = $result->fetch_assoc();
			$this->db->close();
			//echo "called";			
		}
		//var_dump($object);
		return $this->specialCharCleanup( $object);
		//return $object;
	}

	function getChallengeRating($id){
		//var_dump($id);
		$sql="SELECT r.ID, r.NAME, r.PROF_BONUS, r.AC_BONUS, r.ABILITY_BONUS, dd.COUNT AS DCOUNT, dd.DIE AS DDIE FROM CHALLENGE_RATINGS AS r JOIN DMG_DICE as dd ON r.DMG_DIE = dd.ID WHERE r.ID={$id}";
	 	$object=NULL;
		if ($result = $this->db->query($sql)) {
			$object = $result->fetch_assoc();
			$this->db->close();
			//echo "called";			
		}
		//var_dump($object);
		return $this->specialCharCleanup( $object);
		//return $object;
	}

	function executeCommand(){

		header('Access-Control-Allow-Credentials: true');			 
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
	
		$url = parse_url($_SERVER['REQUEST_URI']);
		$chunks = explode('/', $url["path"]);

		
			if(array_key_exists("query",$url)){
				$id=intval($url["query"]);
				if($chunks[2]==="RACES"){
					$output = $this->getRace($id);
				} else if ($chunks[2]==="CLASSES"){
					$output = $this->getClass($id);
				} else if ($chunks[2]==="CHALLENGE_RATINGS"){
					$output = $this->getChallengeRating($id);
				}
				else{
					return -1;
				}
			} else{
				$output = $this->getList($chunks[2]);
			}
		
		//var_dump($output);
		
			if (isset($_SERVER['REQUEST_METHOD'])) {
				header('Content-Type: application/json; charset=utf-8');
			}
					
			$res = json_encode($output);
			// var_dump($output);
			// echo PHP_EOL;
			if(!$res){
				echo json_last_error();
			} else {
				echo $res;
			}
		
	}
}

$conn = new Backend(parse_ini_file ("dbconfig.ini"));
$conn->executeCommand();

?>
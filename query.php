<?php

	new QueryWines();

	class QueryWines{
		function __construct(){
			require_once 'pdo_helper.php';
			$this->dbc = new pdo_helper(array(
				'server' => 'localhost'
				,'user' => 'root'
				,'password' => 'rootaccess'
				,'port' => '3306'
				,'database' => 'bradley_web_wines'
			));
			if($this->dbc === false){
				die("Failed to connect to DB");
			}

			if(isset($_POST['query'])){
				$this->header();
				$this->showQueryChooser();
				$this->showResults();
				$this->footer();
			}else{
				$this->header();
				$this->showQueryChooser();
				$this->footer();
			}
		}

		function header(){
			echo '<!DOCTYPE HTML>
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
					<title>Exploring Wines in a Region</title>
					</head>

					<body bgcolor="white">';
		}

		function footer(){
			echo '</body>
				</html>';
		}

		function showQueryChooser(){
			if($this->dbc->query("SELECT region_name FROM region") === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			$regions = $this->dbc->fetch_all_assoc();

			if($this->dbc->query("SELECT DISTINCT(year) FROM wine ORDER BY year") === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			$years = $this->dbc->fetch_all_assoc();
			echo '<form action="query.php" method="POST">';

			//====== Region Form ======
			echo 'Region:
			<select name="regionName">';
			foreach($regions as $region){
				$selected = "";
				if(isset($_POST['regionName']) && $_POST['regionName'] == $region['region_name']){
					$selected = 'selected = "selected"';
				}
				echo '<option value="'.$region['region_name'].'" '.$selected.'>'.$region['region_name'].'</option>';
			}
			echo '</select><br>';

			//====== Year Form ======
			echo 'Year:
			<select name="year">';
			echo '<option value="All">All</option>';
			foreach($years as $year){
				$selected = "";
				if(isset($_POST['year']) && $_POST['year'] == $year['year']){
					$selected = 'selected = "selected"';
				}
				echo '<option value="'.$year['year'].'" '.$selected.'>'.$year['year'].'</option>';
			}
			echo '</select><br>';

			echo '<input type="hidden" name="query" value="regionName">
			<input type="submit" value="Show Wines">
			</form>
			';
		}

		function showResults(){
			$query = "SELECT wine_id, wine_name, description, year, winery_name
			FROM   winery, region, wine
			WHERE  winery.region_id = region.region_id
			AND    wine.winery_id = winery.winery_id";

			if (isset($_POST['regionName']) && $_POST['regionName'] != "All"){
				$query .= " AND region_name = ?";
			}
			$query .= " ORDER BY wine_name";

			$q=false;
			if (isset($_POST['regionName']) && $_POST['regionName'] != "All"){
				$q = $this->dbc->query($query,array($_POST['regionName']));
			}else{
				$q = $this->dbc->query($query);
			}
			if($q === false){
				die("Query Failed ".$this->dbc->lastError());
			}

			$this->displayWinesList($_POST['regionName']);
		}

		function displayWinesList($regionName){
			$rowsFound = $this->dbc->row_count();
			echo  "{$rowsFound} records found matching your criteria<br>";
			if ($rowsFound <= 0){
				return;
			}

			echo  "Wines of $regionName<br>";

			echo  "\n<table>\n<tr>" .
			"\n\t<th>Wine ID</th>" .
			"\n\t<th>Wine Name</th>" .
			"\n\t<th>Year</th>" .
			"\n\t<th>Winery</th>" .
			"\n\t<th>Description</th>\n</tr>";

			while ($row = $this->dbc->fetch_assoc()){
				echo  "\n<tr>\n\t<td>{$row["wine_id"]}</td>" .
				"\n\t<td>{$row["wine_name"]}</td>" .
				"\n\t<td>{$row["year"]}</td>" .
				"\n\t<td>{$row["winery_name"]}</td>" .
				"\n\t<td>{$row["description"]}</td>\n</tr>";
			}
			echo  "\n</table>";
		}
	}
?>


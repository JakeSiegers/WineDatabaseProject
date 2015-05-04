<?php
	new QueryWines();
	class QueryWines{
		function __construct(){
			require_once($_SERVER['DOCUMENT_ROOT'].'/inc/db/pdo_helper.php');

			$this->dbc = new pdo_helper('db.php');
			if($this->dbc === false){
				die("Failed to connect to DB");
			}

			$this->mainQuery = "SELECT wine_id, wine_name,region_name, description, year, winery_name, wine_type.wine_type
			FROM 	winery, region, wine, wine_type
			WHERE 	winery.region_id = region.region_id
			AND 	wine.winery_id = winery.winery_id
			AND 	wine.wine_type = wine_type.wine_type_id";

			$this->formFilters = array(
				'queries' => array(
					'region_name' 	=> "SELECT region_name FROM region"
					,'year'			=> "SELECT DISTINCT(year) FROM wine ORDER BY year"
					,'winery_name'	=> "SELECT winery_name FROM winery"
					,'wine_type' 	=> "SELECT wine_type as 'wine_type' FROM wine_type"
				),
				'niceNames' => array(
					'region_name' 	=> "Region Name"
					,'year'			=> "Year"
					,'winery_name'	=> "Winery Name"
					,'wine_type' 	=> "Wine Type"
				),
				'tables' => array(
					'region_name' 	=> "region"
					,'year'			=> "wine"
					,'winery_name'	=> "winery"
					,'wine_type' 	=> "wine_type"
				)
			);

			

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
					<link rel="stylesheet" type="text/css" href="style.css" />
					</head>

					<body bgcolor="white">';
		}

		function footer(){
			echo '</body>
				</html>';
		}

		function showQueryChooser(){

			echo '<form action="query.php" method="POST">';

			foreach($this->formFilters['queries'] as $filterName => $filterQuery){

				if($this->dbc->query($filterQuery) === false){
					die("Query Failed ".$this->dbc->lastError());
				}
				$filterChoices = $this->dbc->fetch_all_assoc();
				echo $this->formFilters['niceNames'][$filterName].":";
				echo '<select name="'.$filterName.'">';
				echo '<option value="All">All</option>';
				foreach($filterChoices as $choice){
					$selected = "";
					if(isset($_POST[$filterName]) && $_POST[$filterName] == $choice[$filterName]){
						$selected = 'selected = "selected"';
					}
					echo '<option value="'.$choice[$filterName].'" '.$selected.'>'.$choice[$filterName].'</option>';
				}
				echo '</select><br>';
			}

			echo '<input type="hidden" name="query" value="yes">';
			echo '<input type="submit" value="Show Wines">';
			echo '</form>';
		}

		function showResults(){
			$query = $this->mainQuery;

			$filters = array();
			$filters['names'] = array();
			$filters['values'] = array();

			foreach($this->formFilters['niceNames'] as $filterName => $niceName){
				if (isset($_POST[$filterName]) && $_POST[$filterName] != "All"){
					$query .= " AND ".$this->formFilters['tables'][$filterName].".".$filterName." = ?";
					$filters['names'][] = $niceName;
					$filters['values'][] = $_POST[$filterName];
				}
			}

			$query .= " ORDER BY wine_name";

			$q=false;
			if(count($filters['values'])>0){
				$q = $this->dbc->query($query,$filters['values']);
			}else{
				$q = $this->dbc->query($query);
			}
			if($q === false){
				die("Query Failed ".$this->dbc->lastError());
			}

			$this->displayWinesList($filters);
		}

		function displayWinesList($filters){
			$rowsFound = $this->dbc->row_count();
			echo  "{$rowsFound} records found matching your criteria";

			if(count($filters['names'])>0){
				echo  " - <b>Showing Wines With the following Filters:</b><br>";
				for($i=0;$i<count($filters['names']);$i++){
					echo '<span class="red">'.$filters['names'][$i].' = '.$filters['values'][$i].'</span><br />';
				}
				
			}

			echo  "<table><tr>" .
			"<th>Wine Name</th>" .
			"<th>Year</th>" .
			"<th>Winery</th>" .
			"<th>Wine Type</th>".
			"<th>Region Name</th></tr>";

			while ($row = $this->dbc->fetch_assoc()){
				echo  "<tr>" .
				"<td>{$row["wine_name"]}</td>" .
				"<td>{$row["year"]}</td>" .
				"<td>{$row["winery_name"]}</td>" .
				"<td>{$row["wine_type"]}</td>" .
				"<td>{$row["region_name"]}</td></tr>";
			}
			echo  "</table>";
		}
	}
?>


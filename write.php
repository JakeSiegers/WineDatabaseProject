<?php
	new writeRegions();
	class writeRegions{
		function __construct(){
			require_once 'pdo_helper.php';
			$this->dbc = new pdo_helper('db.php');
			if($this->dbc === false){
				die("Failed to connect to DB");
			}
			$this->header();
			if(isset($_GET['add'])){
				$this->add();
			}
			if(isset($_POST['addConfirm'])){
				$this->addConfirm();
			}
			if(isset($_GET['edit'])){
				$this->edit();
			}
			if(isset($_POST['editConfirm'])){
				$this->editConfirm();
			}
			if(isset($_GET['delete'])){
				$this->delete();
			}
			if(isset($_POST['deleteConfirm'])){
				$this->deleteConfirm();
			}
			$this->baseForm();
			$this->footer();
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

		function add(){
			echo '<form action="write.php" method="post">';
			echo 'New Region: <input type="text" name="newRegion">';
			echo '<input type="submit" value="Add Region">';
			echo '<input type="hidden" name="addConfirm">';
			echo '</form>';
		}
		function addConfirm(){
			$name = $_POST['newRegion'];
			if(strlen($name) == 0){
				echo '<div class="red">Region cannot be blank</div>';
				return;
			}
			if($this->dbc->query("SELECT region_id,region_name FROM region WHERE region_name=?",array($name)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			if($this->dbc->row_count()>0){
				echo '<div class="red">"'.$name.'" region already exists!</div>';
				return;
			}

			if($this->dbc->query("INSERT INTO region (region_name) VALUES (?)",array($name)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			echo '<div class="green">"'.$name.'" region added!</div>';
		}
		function edit(){
			$id = $_GET['edit'];
			if($this->dbc->query("SELECT region_id,region_name FROM region WHERE region_id=?",array($id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			$region = $this->dbc->fetch_assoc();
			echo 'Editing: "'.$region['region_name'].'"<br />';
			echo '<form action="write.php" method="post">';
			echo '<input type="text" name="regionName" value="'.$region['region_name'].'">';
			echo '<input type="submit" value="Edit">';
			echo '<input type="hidden" name="id" value="'.$id.'">';
			echo '<input type="hidden" name="editConfirm">';
			echo '</form>';
		}
		function editConfirm(){
			$id = $_POST['id'];
			$name = $_POST['regionName'];

			if(strlen($name) == 0){
				echo '<div class="red">Region cannot be blank</div>';
				return;
			}

			if($this->dbc->query("SELECT region_id,region_name FROM region WHERE region_name=? AND region_id!=?",array($name,$id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}

			if($this->dbc->row_count()>0){
				echo '<div class="red">"'.$name.'" region already exists!</div>';
				return;
			}

			if($this->dbc->query("UPDATE region SET region_name=? WHERE region_id=?",array($name,$id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}

			echo '<div class="green">"'.$name.'" was saved!</div>';
		}
		function delete(){
			$id = $_GET['delete'];
			if($this->dbc->query("SELECT region_id,region_name FROM region WHERE region_id=?",array($id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			$region = $this->dbc->fetch_assoc();
			echo 'Are you sure you wish to delete: "'.$region['region_name'].'" ?<br />';
			echo '<form action="write.php" method="post">';
			echo '<input type="submit" value="Yes">';
			echo '<input type="hidden" name="id" value="'.$id.'">';
			echo '<input type="hidden" name="deleteConfirm">';
			echo '</form>';
			echo '<form action="write.php" method="post">';
			echo '<input type="submit" value="No">';
			echo '</form>';
		}
		function deleteConfirm(){
			$id = $_POST['id'];
			if($this->dbc->query("SELECT region_id,region_name FROM region WHERE region_id=?",array($id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			$region = $this->dbc->fetch_assoc();
			if($this->dbc->query("DELETE FROM region WHERE region_id=?",array($id)) === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			echo '<div class="green">"'.$region['region_name'].'" was deleted!</div>';
		}
		function baseForm(){
			if($this->dbc->query("SELECT region_id,region_name FROM region") === false){
				die("Query Failed ".$this->dbc->lastError());
			}
			echo '<a href="write.php?add">Add a region</a>';
			echo '<table>';
			$regions = $this->dbc->fetch_all_assoc();
			foreach($regions as $row){
				echo '<tr>';
				echo '<td>'.$row['region_name'].'</td><td><a href="write.php?edit='.$row['region_id'].'">Edit</a></td><td><a href="write.php?delete='.$row['region_id'].'">Delete</a></td>';
				echo '</tr>';
			}
			echo '</table>';

		}
	}

?>
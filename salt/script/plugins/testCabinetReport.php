<?php


// Variables:
$tabname = 'CabinetReport';
$displaylinks = 1;      // 1 = Display HTML links for devices and ports

///////////////////////////////////////////////////////////
$tabhandler['reports']['cabinet_report'] = 'CabinetReport'; // register a report rendering function
$tab['reports']['cabinet_report'] = $tabname; // title of the report tab


function CabinetReport()
{
        global  $displaylinks;

        // Remote jQuery and DataTables files:
        echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.0/css/jquery.dataTables.css">';
        echo '<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>';
        echo '<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>';

        echo '<script>
                $(document).ready(function() {
                    $("#CSVreport").dataTable({
                        "bPaginate": "true",
                        "bLengthChange": "false",
                        "sPaginationType": "full_numbers",
                        "aaSorting": [[ 0, "desc" ]],
                        "iDisplayLength": 20,
                        "stateSave": false,
                        "oLanguage": {
                                "sLengthMenu": \'Display <select>\'+
                                           \'<option value="10">10</option>\'+
                                           \'<option value="20">20</option>\'+
                                           \'<option value="30">30</option>\'+
                                           \'<option value="40">40</option>\'+
                                           \'<option value="50">50</option>\'+
                                           \'<option value="-1">All</option>\'+
                                           \'</select> records\'
                        }
                    });
                });


	//	function rowDropdownSelect(elem) {
	//		var rowSelected = elem.value;
	//		var cabinet = document.getElementById("cabinetSelect");
	//		cabinet.innerHTML = "<option value=" + rowSelected + ">" + rowSelected + "</option>";
//			$.ajax({
//				data: "rowSelected=" + rowSelected,
//				url: "", method: "POST"
//			});
			//document.getElementById("submitForm").submit();			
			//elem.parentElement.submit();
			//alert(rowSelected);
			//var rowSelect = document.getElementById("rowSelect");
			//alert(rowSelect.innerText);
			//rowSelect.innerHTML = "<option>Hey bud</option>";




			// get row select by id
			// get the rack dropbox by id

			// get the selected row value

			// for each corresponding rack value, add <option>...</option> to a string
			// set that string to be the rack select innerHTML

	//	}
                </script>';

        echo "\n";
	$tableheader = 'CabinetReport';
        echo '<div class=portlet>';
	$selectOption = $_POST['select'];
	$selectCabinet= $_POST['cabinetSelect'];
	echo '<h1 id="rowHeader">'. $selectOption .'</h1>';
	$rowSelected = $_POST['rowSelected'];
	$rowcab = $rowSelected;
	$rowcab .= $selectCabinet;
	echo '<h2>'. $rowcab . '</h2>';
	#$testvariable = var rowSelected;	

        echo '<h2>' . $tableheader . '</h2>';
        echo "\n";
    	#echo '<a href="index.php?page=reports&tab=cabinet_report&csv">CSV Export</a>';
	echo "\n";
        echo '<table id="CabinetReport" class="display" cellspacing=20>';
        echo "\n";
        echo '<thead><tr>';
        echo '<th>Unit No</th>';
	echo '<th>Name</th>';
	echo "<th</th>";
        echo '<th>Pool/Group</th>';
        echo '<th></th>';
        echo '</tr></thead>';
        echo "\n";
        echo '<tbody>';
        echo "\n";
	$cabinet = $selectCabinet;
	$row_name = $selectOption;
	$rowpodsql = 'SELECT name from Row ORDER BY name ASC;';
	$cabsql = "SELECT name from Rack where row_name= ? ORDER BY name ASC;";
	$cabResult = usePreparedSelectblade($cabsql,array( $row_name));
	$rowpodResult = usePreparedSelectblade($rowpodsql);
	$rowpodRet = array();
	$cabRet = array();
	$counter = 0;
	$select = '<br><br><form id="selectForm" action="" method="post"><select onchange="this.form.submit()" id="rowSelect" name="select">';
	while($row = $rowpodResult->fetch(PDO::FETCH_ASSOC))
	{
		if ( isset($selectOption) && ($selectOption == $row['name']))
		{		
			$select.='<option selected value ="'.$row['name'].'">'.$row['name'].'</option>';
		}
		else
		{
			$select.='<option value ="'.$row['name'].'">'.$row['name'].'</option>';
		}
		$rowpodRet[$counter['name']]=$row['name'];
		#echo $row['name'];
		$counter= $counter + 1;
	}
	$select.='</select><input type="text" placeholder="Cabinet Name ex. C09" id="cabinetSelect" name ="cabinetSelect">';
#<option selected value = " "></option>';
	while($line = $cabResult->fetch(PDO::FETCH_ASSOC))
	{	
		$cabRet[$line['name']]=$line['name'];
		/*if( isset($selectCabinet) && ($selectCabinet == $line['name']))
		{	
			
			#$select.='<option value ="'.$line['name'].'">'.$line['name'].'</option>';
		}		
		else	
		{
			$select.='<option value ="'.$line['name'].'">'.$line['name'].'</option>';
		}*/
	}
	$select.='</select><input type="checkbox" name="csv" value="1"> CSV Export <input type="submit" name="submit" value="Go"/></form>';
	echo $select;
	$selectOption = $_POST['select'];
	$selectCabinet = $_POST['cabinetSelect'];
	if ( isset($selectCabinet) && ( !in_array( $selectCabinet, $cabRet)))
	{
		$cabinethtml = "<br><h2>Available cabinets are</h2> <p style='color:rebeccapurple'>| ";
		foreach ( $cabRet as $z)
		{
			$cabinethtml.=$z;
			$cabinethtml.=' | ';		
		}
		$cabinethtml.='</p>';
		echo $cabinethtml;

	}
		


	
	#echo $rowpodRet[['name']];
	if ( isset($selectOption))
	{
		$selectOption = $_POST['select'];
		$row_name = $selectOption;
		$sql = "SELECT T1.unit_no,T2.name,T2.comment from ( SELECT DISTINCT unit_no,object_id from RackSpace where rack_id=(select id from Rack where name= ? and row_name= ? )) AS T1 JOIN ( select name,id,comment from Object where id=ANY(select object_id from RackSpace where rack_id=(select id from Rack where name= ? and row_name= ? ))) as T2 on T1.object_id = T2.id ORDER BY unit_no DESC;";	
		$result = usePreparedSelectblade($sql,array($cabinet,$row_name,$cabinet,$row_name));
		$ret = array();

		while($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			$ret[$row['unit_no']]=$row['unit_no'];
			$ret[$row['name']]=$row['name'];
			$ret[$row['comment']]=$row['comment'];
			echo '<tr><td>';
			echo $row['unit_no'];
			echo '</td><td>';		
			echo $row['name'];
			echo '</td><td>';
			echo $row['comment'];
			echo '</td></tr>';
		}
	
	#if ( isset($_GET['csv']) ) 
	if (  ( $_SERVER['REQUEST_METHOD'] == 'POST' ) && ( isset( $_POST['csv'] ) ) )	
	{
		$aResult = array();
		$delimiter=';';
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename=export_'.date("Ymdhis").'.csv');
		header('Pragma: no-cache');
		header('Expires: 0');
		ob_clean();
		$outstream = fopen("php://output", "w");

		#$cabinet = 'C14';
		#$row_name = $selectOption;
		$sql = "select T1.unit_no,T2.name,T2.comment from ( select distinct unit_no,object_id from RackSpace where rack_id=(select id from Rack where name= ? and row_name= ?)) AS T1 JOIN ( select name,id,comment from Object where id=ANY(select object_id from RackSpace where rack_id=(select id from Rack where name= ? and row_name= ? ))) as T2 on T1.object_id = T2.id ORDER BY unit_no DESC;";	
		$result = usePreparedSelectblade($sql, array( $cabinet,$row_name,$cabinet,$row_name));
		$ret = array();
	      	while($row = $result->fetch(PDO::FETCH_ASSOC))
			{	
				$aCSVRow = array();
				$aCSVRow[0]=$row['unit_no'];
				$aCSVRow[1]=$row['name'];
				$aCSVRow[2]=$row['comment'];
				fputcsv( $outstream, $aCSVRow ,$delimiter);
			}
				fclose($outstream);

		exit(0); # Exit normally after send CSV to browser
		}
	}
}
?>


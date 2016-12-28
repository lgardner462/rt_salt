<?php


// Variables:
$tabname = 'CSVReport';
$displaylinks = 1;      // 1 = Display HTML links for devices and ports


///////////////////////////////////////////////////////////
$tabhandler['reports']['csvreport'] = 'CSVReport'; // register a report rendering function
$tab['reports']['csvreport'] = $tabname; // title of the report tab

function CSVReport()
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
                </script>';

        echo "\n";
	$tableheader = 'CSVReport';
        echo '<div class=portlet>';
        echo '<h2>' . $tableheader . '</h2>';
        echo "\n";
    	echo '<a href="index.php?page=reports&tab=csvreport&csv">CSV Export</a>';
	echo "\n";
        echo '<table id="CSVreport" class="display">';
        echo "\n";
        echo '<thead><tr>';
        echo '<th>Cable ID</th>';
        echo '<th>Device 1</th>';
;
        echo '<th>Device 2</th>';

        echo '</tr></thead>';
        echo "\n";
        echo '<tbody>';
        echo "\n";

        $allports = fetchPortList('IF(la.porta, pa.id, pb.id) IS NOT NULL');
        $cid = 0;
        foreach ( $allports as $port ) {
                $allporttypes[$port['id']] = $port['oif_name'];

                if ( $port['linked'] != 1 ) {
                        continue;
                }

                if ( $done[$port['id']] == 1 ) {
                        continue;
                } else {
                        $cid++;
                        $cabletable[$cid]['cableid'] = $port['cableid'];
                        if ( $displaylinks == 2 ) {
                                $cabletable[$cid]['device1'] = formatPortLink($port['object_id'],$port['object_name'],NULL,NULL);
                                $cabletable[$cid]['port1']   = formatPortLink($port['object_id'],NULL,$port['id'],$port['name']);
                        } else {
                                $cabletable[$cid]['device1'] = $port['object_name'];
                                $cabletable[$cid]['port1']   = $port['name'];
                        }
                        $cabletable[$cid]['port1id'] = $port['id'];
                        $cabletable[$cid]['type1']   = $port['oif_name'];
                        if ( $displaylinks == 2 ) {
                                $cabletable[$cid]['device2'] = formatPortLink($port['remote_object_id'],$port['remote_object_name'],NULL,NULL);
                                $cabletable[$cid]['port2']   = formatPortLink($port['remote_object_id'],NULL,$port['remote_id'],$port['remote_name']);
                        } else {
                                $cabletable[$cid]['device2'] = $port['remote_object_name'];
                                $cabletable[$cid]['port2']   = $port['remote_name'];
                        }
                        $cabletable[$cid]['port2id'] = $port['remote_id'];
                        $cabletable[$cid]['type2']   = ''; # missing from fetchPortList() add later from $allporttypes being created;
                        $done[$port['remote_id']] = 1;
                }
        }

        foreach ( $cabletable as $cable ) {
                
	        echo '<tr>';
                echo '<td>';
                echo $cable['cableid'];
                echo '</td><td>';
                echo $cable['device1'];
                echo '</td><td>';
                echo $cable['device2'];
                echo '</td><td>';
                echo '</td><td>';
                echo '</td>';
                echo '</tr>';
                echo "\n";;
        }




        echo '</tbody></table><br/><br/>';
        echo '</div>';
if ( isset($_GET['csv']) ) {
	$aResult = array();
	$delimiter=';';
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=export_'.date("Ymdhis").'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
	ob_clean();
        $outstream = fopen("php://output", "w");

       foreach ( $cabletable as $cable ) {
    		$aCSVRow = array();
           	$aCSVRow[0] = $cable['cableid'];
           	$aCSVRow[1] = $cable['device1'];
            	$aCSVRow[2] = $cable['device2'];
		fputcsv( $outstream, $aCSVRow ,$delimiter);
        }
		fclose($outstream);


        exit(0); # Exit normally after send CSV to browser
}
}

?>

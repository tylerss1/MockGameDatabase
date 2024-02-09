<html>
<!-- MODIFYING TUTORIAL CODE -->
	<head>
        <title>World Management</title>
    </head>

	<body>


        <a href="https://www.students.cs.ubc.ca/~baekjong/home-page.html">Go Home</a>
        <hr>

		<h1>World Management</h1>

		<h2>Get average KDR in a dungeon</h2>

		<form method="GET" action="world-management.php"> 
            <select name='dungeonName'>
                <option value="">-- Select --</option>
                <?php
                if (connectToDB()) {

                    global $db_conn;
                    $result = executePlainSQL("SELECT l_name FROM dungeon");
                    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                        echo '<option value = "' . $row[0] . '"> ' . $row[0] . '</option>';
                    }
                    
                    disconnectFromDB();
                }
                ?>
            </select> </p>
            <input type="hidden" id="averageKDRRequest" name="averageKDRRequest">
            <input type="submit"  name="averageKDR"></p>
        </form>

		<hr>

        <h2>Get lowest average KDR of dungeons</h2>
            
        <form method="GET" action="world-management.php">
            <input type="hidden" id="lowestAverageKDRRequest" name="lowestAverageKDRRequest">
            <input type="submit" name="lowestAverageKDRRequest"></p>
        </form>

        <hr>

		<h2>Update mob attribute</h2>

        <form method="POST" action="world-management.php"> <!--refresh page when submitted-->
            <select name='mob_select'>
                <option value="">-- Select --</option>
                <?php
                if (connectToDB()) {

                    global $db_conn;
                    $result = executePlainSQL("SELECT n_name, n_id FROM npc WHERE n_id IN (SELECT n_id FROM mob)");
                    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                        echo '<option value = "' . $row['N_ID'] . '"> ' . $row['N_NAME'] . '</option>';
                    }
                    
                    disconnectFromDB();
                }
                ?>
            </select> </p>
            <input type="hidden" id="updateMobAttributeRequest" name="updateMobAttributeRequest">
            <input type="radio" name="mob_attributes" value="health">
            <label for="health">Health</label><br><br>
            <input type="radio" name="mob_attributes" value="damage">
            <label for="damage">Damage</label><br><br>
            <input type="radio" name="mob_attributes" value="kills">
            <label for="kills">Kills</label><br><br>
            <input type="radio" name="mob_attributes" value="deaths">
            <label for="deaths">Deaths</label><br><br>
            New Value: <input type="text" name="new_value"> <br><br>
            <input type="submit" value="Update" name="updateSubmit"></p>
        </form>

		<hr>

		<h2>Add item</h2>

        <form method="POST" action="world-management.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertItemRequest" name="insertItemRequest">
            Item Name: <input type="text" name="i_name"> <br><br>
            Rarity: 
            <select name='rarity'>
                <option value="">-- Select --</option>
                <option value="NULL">NULL</option>
                <option value="Common">Common</option>
                <option value="Rare">Rare</option>
                <option value="Epic">Epic</option>
                <option value="Legendary">Legendary</option>
            </select>
            <br><br>
            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>
		
        <hr>

        <h2>Show items sold in every town</h2>
        <form method="GET" action="world-management.php">
            <input type="hidden" id="soldInEveryTownRequest" name="soldInEveryTownRequest">
            <input type="submit" name="soldInEveryTown"></p>
        </form>

        <h2> Search </h2>
        <?php
        if (! isset($_GET['tableNameSubmit'])) {
            selectTable();
        }
        ?>
        

	<?php

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr); 

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_rccryer", "a91600189", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }
		
        function handleAverageKDRRequest() {
            global $db_conn;
			$dungeon_name = $_GET['dungeonName'];
            $result = executePlainSQL("SELECT AVG(kills), AVG(deaths) FROM mob m, spawns s, dungeon d WHERE m.n_id = s.n_id and s.l_name = d.l_name and d.l_name = '" . $dungeon_name . "'");
			
            echo "&nbsp <b>Kills</b> &nbsp&nbsp&nbsp <b>Deaths</b> <br>";
            while (($row = oci_fetch_array($result)) != false) {    
                echo "". $row[0] . " " . $row[1] . "<br>";
                echo $row[0]/$row[1];
            }
        }
        
        function handleLowestAverageKDRRequest() {
            $result = executePlainSQL("SELECT MIN(x.kdr) 
                                       FROM (SELECT (AVG(kills) / AVG(deaths)) as kdr FROM mob m, dungeon d, spawns s WHERE m.n_id = s.n_id and s.l_name = d.l_name GROUP BY d.l_name) x");   
            while (($row = oci_fetch_array($result)) != false) {    
                echo "". $row[0] . " " . $row[1] . "<br>";
            }
        }

        function handleUpdateMobAttributeRequest() {
            global $db_conn;

            $mob_id = $_POST['mob_select'];
            $attribute = $_POST['mob_attributes'];
            $new_value = $_POST['new_value'];
            if ($attribute == "health"){
                $attribute_to_update = 'health';
                echo "Health updated";
            } else if ($attribute == "damage"){
                $attribute_to_update = 'damage';
                echo "Damage updated";
            } else if ($attribute == "kills"){
                $attribute_to_update = 'kills';
                echo "Kills updated";
            } else {
                $attribute_to_update = 'deaths';
                echo "Deaths updated";
            }    

            echo 'Testing' . $mob_id . $attribute . $new_value . 'End';
            executePlainSQL("UPDATE mob SET $attribute_to_update ='" . $new_value . "' WHERE n_id='" . $mob_id . "'");
            OCICommit($db_conn);
        }

        function handleInsertItemRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['i_name'],
                ":bind2" => $_POST['rarity']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("INSERT INTO item VALUES (:bind1, :bind2)", $alltuples);
            OCICommit($db_conn);
        }

        function handleSoldInEveryTownRequest() {
            global $db_conn;

            $result = executePlainSQL(" SELECT i.i_name FROM item i
                                       WHERE NOT EXISTS ((SELECT t.l_name FROM town t) MINUS (SELECT v.l_name FROM villager v, sells s WHERE s.n_id = v.n_id and s.i_name = i.i_name))");   
            while (($row = oci_fetch_array($result)) != false) {    
                echo "". $row[0] . " " . $row[1] . "<br>";
            }
        }

        function selectTable() {
            echo '
            <form method="GET" action="world-management.php">
            <select name="searchTable">
                <option value="">-- Select --</option>
                <option value="mob">Mob</option>
                <option value="item">Item</option>
                <option value="rv">Rarity</option>
                <option value="player">Player</option>
                <option value="lvl_stats">Level Stats</option>
            </select> </p>
            <input type="submit" name="tableNameSubmit"></p>
            </form> ';
        }

        function viewAttributes() {
            
            $table = $_GET['searchTable'];
            echo '
                    <form method="GET" action="world-management.php">
                    <select name="attr">';
            switch ($table) {
                case "mob":
                echo '
                        <option value="">-- Select --</option>
                        <option value="n_id">ID</option>
                        <option value="kills">Kills</option>
                        <option value="deaths">Deaths</option>
                        <option value="health">Health</option>
                        <option value="damage">Damage</option>';
                break;

                case "item":
                echo '
                        <option value="">-- Select --</option>
                        <option value="i_name">Item Name</option>
                        <option value="rarity">Rarity</option>';
                break;

                case "rarity":
                echo '
                        <option value="">-- Select --</option>
                        <option value="value">Value</option>
                        <option value="r_name">Rarity</option>';
                break;

                case "player":
                echo '
                        <option value="">-- Select --</option>
                        <option value="p_name">Player Name</option>
                        <option value="p_id">Player ID</option>
                        <option value="join_date">Join Date</option>
                        <option value="money">Money</option>
                        <option value="lvl">Level</option>
                    <option value="l_name">Location</option>';
                break;

                case "lvl_stats":
                echo '
                        <option value="">-- Select --</option>
                        <option value="lvl">Level</option>
                        <option value="strength">Strength</option>
                        <option value="health">Health</option>';
                break;
            }
            echo '
            </select>
            <select name="operator">
                <option value="">-- Select --</option>
                <option value="=">=</option>
                <option value="<>">/=</option>
                <option value=">">></option>
                <option value="<"><</option>
                <option value=">=">>=</option>
                <option value="<="><=</option>
            </select>
            <input type="text" name="value"> <br><br>
            <?php echo $table ?>
            
            <input type="hidden" id="searchRequest" name="searchRequest" value= ' . $table. '>
            <input type="submit" name="searchSubmit"></p>
            </form>';   
        }

        function handleSearchRequest() {
            global $db_conn;
            $table = $_GET['searchRequest'];
            $attr = $_GET['attr'];
            $op = $_GET['operator'];
            $val = $_GET['value'];
            $result = executePlainSQL("SELECT * FROM $table WHERE $attr $op $val");
            $count = 0;
            echo '<table>';
            echo '<tr>';
            switch ($table) {
                case "mob":
                echo '
                    <th>ID</th>
                    <th>Kills</th>
                    <th>Deaths</th>
                    <th>Health</th>
                    <th>Damage</th>';
                echo '</tr>';
                $count = 5;
                break;

                case "item":
                echo '
                    <th>Item Name</th>
                    <th>Rarity</th>';
                echo '</tr>';
                $count = 2;
                break;

                case "rv":
                echo '
                    <th>Value</th>
                    <th>Rarity</th>';
                echo '</tr>';
                $count = 2;
                break;

                case "player":
                echo '
                    <th>Player Name</th>
                    <th>Player ID</th>
                    <th>Join Date</th>
                    <th>Money</th>
                    <th>Level</th>
                    <th>Location</th>';
                echo '</tr>';
                $count = 6;
                break;

                case "lvl_stats":
                echo '
                    <th>Level</th>
                    <th>Strength</th>
                    <th>Health</th>';
                echo '</tr>';
                $count = 3;
                break;
            }
            while (($row = oci_fetch_array($result)) != false) {
                echo '<tr>';
                for ($i = 0; $i < $count; $i++) {
                    echo '<td>' . $row[$i] . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }

        #"UPDATE mob SET $attribute_to_update ='" . $new_value . "' WHERE n_id='" . $mob_id . "'"

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('updateMobAttributeRequest', $_POST)) {
                    handleUpdateMobAttributeRequest();
                } else if (array_key_exists('insertItemRequest', $_POST)) {
                    handleInsertItemRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('averageKDRRequest', $_GET)) {
                    handleAverageKDRRequest();
                } else if (array_key_exists('lowestAverageKDRRequest', $_GET)) {
                    handleLowestAverageKDRRequest();
                } else if (array_key_exists('soldInEveryTownRequest', $_GET)) {
                    handleSoldInEveryTownRequest();
                } else if (array_key_exists('tableNameSubmit', $_GET)){
                    viewAttributes();
                } else if (array_key_exists('searchRequest', $_GET)) {
                    handleSearchRequest();
                }
                disconnectFromDB();
            }
        }

		if (isset($_POST['updateMobAttributeRequest']) || isset($_POST['insertItemRequest'])) {
            handlePOSTRequest();
        } else if (isset($_GET['averageKDRRequest']) || isset($_GET['lowestAverageKDRRequest']) ||
                   isset($_GET['soldInEveryTownRequest']) || isset($_GET['tableNameSubmit']) || isset($_GET['searchRequest'])) {
            handleGETRequest();
        }
	?>
	</body>

</html>

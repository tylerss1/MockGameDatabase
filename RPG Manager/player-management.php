<html>
<!-- MODIFYING TUTORIAL CODE -->

<title> Player Management</title>


<body>
<a href="https://www.students.cs.ubc.ca/~baekjong/home-page.html">Go Home</a>
<hr>

<h1>Player Management</h1>
<h2> Create Player </h2>
<form method="POST" action="player-management.php">
    <input type="hidden" id="createPlayerRequest" name="createPlayerRequest">
    user id: <input type="text" name="uidCreate">
    username: <input type="text" name="unameCreate"> <br />
    <input type="submit" value="create" name="createSubmit"></p>
</form>

<h2> Administer Player </h2>
<form method="POST" action="player-management.php">
    <select name='pnameUpdate'>
        <option value="">-- Select --</option>
        <?php
        if (connectToDB()) {

            global $db_conn;
            $result = executePlainSQL("select p_id, p_name from player");
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<option value = "' . $row['P_ID'] . '"> ' . $row['P_NAME'] . '</option>';
            }
            
            disconnectFromDB();
        }
        ?>
    </select> </p>
    <input type="submit" value="change" name="changeSubmit">
    new username: <input type="text" name="unameChange"> </p>
    <input type="submit" value="delete" name="deleteSubmit"></p>
</form>

<h2> View Player Attributes</h2>
<form method="GET" action="player-management.php">
    <select name='pnameViewAttr'>
            <option value="">-- Select --</option>
            <?php
            if (connectToDB()) {
                global $db_conn;
                $result = executePlainSQL("select p_id, p_name from player");
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo '<option value = "' . $row['P_ID'] . '"> ' . $row['P_NAME'] . '</option>';
                }
                disconnectFromDB();
            }
            ?>
    </select>
    <input type="hidden" id="viewAttrRequest" name="viewAttrRequest">
    <input type="checkbox" id= "a_id" name= "a_id">ID</option>
    <input type="checkbox" id= "a_username" name= "a_username">Username</option>
    <input type="checkbox" id= "a_joindate" name= "a_joindate">Join Date</option>
    <input type="checkbox" id= "a_money" name= "a_money">Money</option>
    <input type="checkbox" id= "a_level" name= "a_level">Level</option>
    <input type="checkbox" id= "a_loc" name= "a_loc">Location</option></p>
    <input type="submit" value="view" name="viewAttrSubmit"></p>
</form>


<h2> View Player Inventory</h2>
<form method="GET" action="player-management.php">
    <input type="hidden" id="viewInventoryRequest" name="viewInventoryRequest">
    User: 
    <select name='pnameView'>
        <option value="">-- Select --</option>
        <?php
        if (connectToDB()) {
            global $db_conn;
            $result = executePlainSQL("select p_id, p_name from player");
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<option value = "' . $row['P_ID'] . '"> ' . $row['P_NAME'] . '</option>';
            }
            disconnectFromDB();
        }
        ?>
    </select>
    <input type="submit" value="view" name="viewInventorySubmit"></p>
</form>

<form method="GET" action="player-management.php">
    <input type="hidden" id="viewPlayersRequest" name="viewPlayersRequest">
    View Players:
    <input type="submit" value="view" name="viewPlayerSubmit"></p>
</form>

</body>
<?php

    function handleCreatePlayerRequest() {
        global $db_conn;

        $tuple = array (
            ":userid" => $_POST['uidCreate'],
            ":username" => $_POST['unameCreate'],
            ":createdate"  => date ('Y-m-d'),
            ":usermoney" => "0",
            ":userlevel" => "1",
            ":starttown" => "Cool Town"
        );

        $alltuples = array (
            $tuple
        );
        
        executeBoundSQL("INSERT INTO player VALUES (:userid,:username, to_date(:createdate,'yyyy-mm-dd'), :usermoney, :userlevel, :starttown)",$alltuples);
        OCICommit($db_conn);
    }

    function handleDeletePlayerRequest() {
        global $db_conn;
        executePlainSQL("DELETE FROM player WHERE p_id = " . $_POST['pnameUpdate'] . "");
        OCICommit($db_conn);
    }

    function handleChangeUsernameRequest() {
        global $db_conn;
        executePlainSQL("UPDATE player set p_name = '" . $_POST['unameChange'] . "' WHERE p_id = " . $_POST['pnameUpdate'] . "");
        OCICommit($db_conn);
    }

    function handleViewInventoryRequest() {
        global $db_conn;
        // echo $_POST['uidChange'];
        
        $result = executePlainSQL("SELECT p_name, i_name FROM owns o, player p WHERE o.p_id = p.p_id AND o.p_id = " . $_GET['pnameView'] . ""); 
        printItems($result);
        // WHERE p_id = " . $_POST['uidView'] . ""
        OCICommit($db_conn);
    }

    function printItems($result) {
        echo "<table>";
        echo "<tr><th>Inventory: </th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["P_NAME"] . " has " . $row["I_NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
            // echo $row[0];
        }

        echo "</table>";
    }

    function handleViewAttrRequest() {
        global $db_conn;
        $attr = "";
        $count = 0;
        
        if (array_key_exists('a_id', $_GET)) {
            $attr .= "P_ID, ";
            $count += 1;
        }
        if (array_key_exists('a_username', $_GET)) {
            $attr .= "P_NAME, ";
            $count += 1;
        }
        if (array_key_exists('a_joindate', $_GET)) {
            $attr .= "JOIN_DATE, ";
            $count += 1;
        }
        if (array_key_exists('a_money', $_GET)) {
            $attr .= "MONEY, ";
            $count += 1;
        }
        if (array_key_exists('a_level', $_GET)) {
            $attr .= "LVL, ";
            $count += 1;
        }
        if (array_key_exists('a_loc', $_GET)) {
            $attr .= "L_NAME, ";
            $count += 1;
        }

        if ($count != 0){
            $attr = substr($attr,0,-2);
        }
        
        $result = executePlainSQL("SELECT $attr FROM player WHERE P_ID = " . $_GET['pnameViewAttr'] . "");

        $rows = explode(", ", $attr); 
        echo "<table>";
        echo "<tr>";
        
        for ($i = 0; $i < $count; $i++){
            echo "<th>". $rows[$i] ."</th>";
        }
        echo "</tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)){
            echo "<tr>";
            for ($i = 0; $i < $count; $i++){
                echo "<td>" . $row[$i] . "</td>";
            }
        }
        echo "</tr>"; 
        echo "</table>";
        
        
    }

    function printResult($result) { //prints results from a select statement
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>JoinDate</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["P_ID"] . "</td><td>" . $row["P_NAME"] . "</td><td>" . $row["JOIN_DATE"] . "</td></tr>"; //or just use "echo $row[0]" 
        }

        echo "</table>";
    }

    function handleTableRequest() {
		global $db_conn;
		$result = executePlainSQL("select * from player");
		
		printResult($result);
	}


    function connectToDB() {
        global $db_conn;

        $db_conn = OCILogon(ora_rccryer, a91600189, "dbhost.students.cs.ubc.ca:1522/stu");

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

    //this tells the system that it's no longer just parsing html; it's now parsing PHP

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

    // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest() {
        if (connectToDB()) {
            if (array_key_exists('createSubmit', $_POST)) {
                handleCreatePlayerRequest();
            } else if (array_key_exists('deleteSubmit', $_POST)) {
                handleDeletePlayerRequest();
            } else if (array_key_exists('changeSubmit', $_POST)) {
                handleChangeUsernameRequest();
            }

            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest() {
        if (connectToDB()) {
            if (array_key_exists('viewInventorySubmit', $_GET)) {
                handleViewInventoryRequest();
            } else if (array_key_exists('viewPlayerSubmit', $_GET)) {
                handleTableRequest();
            } else if (array_key_exists('viewAttrSubmit', $_GET)) {
                handleViewAttrRequest();
            }
            
            disconnectFromDB();
        }
    }

    if (isset($_POST['createSubmit']) || isset($_POST['deleteSubmit']) || isset($_POST['changeSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['viewInventoryRequest']) || isset($_GET['viewPlayersRequest']) || isset($_GET['viewAttrSubmit'])) {
        handleGETRequest();
    }
?>
</html>

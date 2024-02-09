<!-- code borrowed and modified from tutorial 7 -->
<html>
    <head>
        <title>Server Management</title>
    </head>

    <body>
        <a href="https://www.students.cs.ubc.ca/~baekjong/home-page.html">Go Home</a>
        <hr>

        <h1>Server Management</h1>

        <h2>Display all Player usernames from a Server</h2>
        <form method="POST" action="server-management.php"> <!--refresh page when submitted-->
            <input type="hidden" id="displayPlayersRequest" name="displayPlayersRequest">
            Server Region: <input type="text" name="serverName"> <br /><br />
            Server Number: <input type="text" name="serverNum"> <br /><br />
            <input type="submit" value="Display" name="displaySubmit"></p>
        </form>

        <hr />

        <h2>List Active Servers over a certain number of Players</h2>

        <form method="POST" action="server-management.php"> <!--refresh page when submitted-->
            <input type="hidden" id="listRequest" name="listRequest">
            Number of Players: <input type="text" name="numPlayers"> <br /><br />
            <input type="submit" value="Display" name="listSubmit"></p>
        </form>

        <hr />


        <?php
                //this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it >

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
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

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
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will n>
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

	function printServers($result) { //prints results from a select statement
            echo "<table>";
            echo "<tr><th>Region</th><th>Number</th><th># Users</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["R_NAME"] . "</td><td>" . $row["S_NUM"] . "</td><td>" . $row["CNT"] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
	
	function printUsers($result) { //prints results from a select statement
            echo "<table>";
            echo "<tr><th>Username</th><th>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["P_NAME"] . "</td><td>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function handleListRequest() {
            global $db_conn;

            $players = $_POST['numPlayers'];

	        $result = executePlainSQL("SELECT r_name, s_num, COUNT(p_id) as cnt FROM is_on WHERE con_status = 'Online' GROUP BY r_name, s_num HAVING COUNT(p_id) > $players");
	        printServers($result);
           
            OCICommit($db_conn);
        }

        function handleDisplayRequest() {
            global $db_conn;
            
            $serverNum = $_POST['serverNum'];
            $serverName = $_POST['serverName'];

            $result = executePlainSQL("SELECT p_name FROM is_on i, player p WHERE i.p_id = p.p_id and i.s_num = $serverNum and i.r_name = '$serverName'");
		
            printUsers($result);
	    
            OCICommit($db_conn);
        }



	// HANDLE ALL POST ROUTES
        // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove >
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('displayPlayersRequest', $_POST)) {
                    handleDisplayRequest();
                } else if (array_key_exists('listRequest', $_POST)) {
                    handleListRequest();
                }

                disconnectFromDB();
            }
        }

        if (isset($_POST['listSubmit']) || isset($_POST['displaySubmit'])) {
            handlePOSTRequest();
        }
                ?>
        </body>
</html>

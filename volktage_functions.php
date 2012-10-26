<?php
// All functions required for the Volktage project are called from this file.

//============== CREATE NEW USER AND/OR NEW ORDER ======================

function new_user_if_needed($username, $user_id, $identifier, $db_handle) {
    # If user has not logged in before, this creates their account

    $SQL = "SELECT * FROM users
            WHERE Username = '$username'";
            
    $result = mysql_query($SQL, $db_handle);
    
    if(mysql_num_rows($result) === 0) {
        echo "User " . $username . " not found.<br><br>";
      
        //Add Username and ID to user table
        
        $INS = "INSERT INTO users (Username, userID, openID)
               VALUES ('$username', '$user_id', '$identifier')";
        $inserted = mysql_query($INS, $db_handle);
        
        if($inserted) {
          echo "New user '" . $username . "' successfully created.<br><br>";
        }
    }
}

function add_new_order_record($user_id, $db_handle){
    
    $order_id = uniqid();
          
    $OT_INS = "INSERT INTO volktageorders (userid, orderName, orderID)
               VALUES ('$user_id', '$order_id', '$order_id')"; //user will have option to change order name
    $new_order = mysql_query($OT_INS, $db_handle)
    or die ("Query failed: " . mysql_error() . "<br>Actual Query: " . $OT_INS);
      
    if($new_order) {
        echo "New order record inserted.<br>
              Order name: " . $order_id . "<br><br>";
    }
    return $order_id;
}

function create_order_table ($order_id, $db_handle) {
  
    $ORDER = "CREATE TABLE $order_id
              (
              component_type VARCHAR(30) NOT NULL UNIQUE,
              PRIMARY KEY(component_type),
              component_name VARCHAR(80) NOT NULL,
              cost INT NOT NULL,
              id int NOT NULL
              )";
    $create_order = mysql_query($ORDER, $db_handle)
    or die ("Query failed: " . mysql_error() . "<br>Actual Query: " . $ORDER);
              
    if($create_order) {
        echo "New order table created. <br>
              Order table name: " . $order_id . "<br><br>";
    }
}

function populate_order_table ($order_id, $db_handle, $master_list) {
    
    $total = 0;
    
    echo "Session username: " . $_SESSION['username'] . "<br>";
    $function_result = '';
    
    $components = $_SESSION['components'];
    
    foreach($components as $component){
          
        //Select a row from the master list
        $SEL = "SELECT * FROM $master_list
        WHERE Component_Name = '$component'";
        $selected = mysql_query($SEL, $db_handle);
        
        //Populate an array with the values from that row.
        //Assign variables based on fields within the array
        while($spec = mysql_fetch_assoc($selected)) {
            $component_type = $spec['Component_Type'];
            $component_name = $spec['Component_Name'];
            $cost = $spec['Cost'];
            $total += $cost;
            $id = $spec['ID'];
        
            //Insert records into the new order from the
            //master list based on the 'Component_Type'.
            //If that key is already present, replace with new values.
            $INS = "INSERT INTO $order_id SELECT * FROM $master_list
                    WHERE Component_Type = '$component_type'
                    ON DUPLICATE KEY UPDATE Component_Name = '$component_name',
                                            Cost = '$cost',
                                            ID = '$id'";
        
            $result = mysql_query($INS, $db_handle)
            or die ("Query failed: " . mysql_error() . "<br><br>" . " Actual query: " . $INS);
            
            //Check to see if variables are updating properly.
            //echo "<br>" . "Query Check:" . " " . $INS . "<br>";
            }
    }
    //Confirm records have been added/changed.
            echo "Order records successfully stored." . "<br><br>";
    }

function assign_session_components() {
    
    $cpu = $_POST['CPU'];
    $motherboard = $_POST['Motherboard'];
    $videocard = $_POST['Video_Card'];
    $_SESSION['components'] = array("$cpu", "$motherboard", "$videocard");
    $components = $_SESSION['components'];
}

//==================== RE-NAME AN ORDER====================

function name_this_build() {
    
    echo '<form action="volktage_review_radios.php" method="post">
              Name This Build:<input name="new_name" type="text" size="30">
              <input type="submit" value="Name It">
          </form>';
}

function update_order_name($new_name, $order_id, $db_handle, $master_list) {
    
    $new_name = mysql_real_escape_string($new_name);
    
    $SEL = "UPDATE volktageorders
            SET orderName = '$new_name'
            WHERE orderID = '$order_id'";
            
    $updated_name = mysql_query($SEL, $db_handle)
    or die("Query failed: " . mysql_error() . "<br>Actual query: " . $SEL);
    
    if($updated_name){
        echo "This order shall henceforth be known as '" . $new_name . "'.<br><br>";
    }
}

//================ MODIFY AN EXISTING BUILD =====================

function display_existing_builds($user_id, $db_handle) {
    
    $ORD = "SELECT * FROM volktageorders
            WHERE userID = '$user_id'";
    $existing = mysql_query($ORD, $db_handle);
        
    $existing_orders = array();
        
    while($order = mysql_fetch_array($existing)) {
        $existing_orders[] = $order['orderName'];
    }
        
    echo "Would you like to modify one of your builds? <br>";
    echo '<form action="volktage_review_radios.php" method="post">
          <select name="modify_order">';
          
    foreach($existing_orders as $order_name) {
        echo '<option value="'.$order_name.'">' . $order_name . '</option><br><br>';
    }
    
    echo '<input type="submit" value="Modify This Build">';
    echo '</select>
          </form>';
}

function generate_radios($db_handle, $master_list, $order_id) {
   
    $the_order = array();

    # Get all component names from the Order, a dedicated table named '$order_id'
    $SEL = "SELECT *
            FROM $order_id";
    $order_items = mysql_query($SEL, $db_handle)
    or die ("Query failed: " . mysql_error() . "<br>Actual query: " . $SEL);
    
    while($item = mysql_fetch_array($order_items)) {
        $the_order[] = $item['component_name'];
    }

    #Get all component names and types from the Master List table
    $LIST = "SELECT *
             FROM $master_list";
    $names = mysql_query($LIST, $db_handle);
    
    while($spec = mysql_fetch_array($names)) {
        $component_type = $spec['Component_Type'];
        $component_name = $spec['Component_Name'];
        
        #If a component name is in both the Master List and the Order, generate checked radio.
        #Else, generate unchecked.
        if(in_array($component_name, $the_order)) {
            echo '<input type="radio" name ="'.$component_type.'" value="'.$component_name.'" checked="checked" />'
            . $component_name . "<br><br>";
        }else{
            echo '<input type="radio" name ="'.$component_type.'" value="'.$component_name.'" />'
            . $component_name . "<br><br>";
            }
        }
    }

function generate_blank_radios($db_handle, $master_list) {

    //Get all data from Master List
    $LIST = "SELECT *
             FROM $master_list";
    $names = mysql_query($LIST, $db_handle);
    
    //Filter for component Type and component Name
    while($spec = mysql_fetch_array($names)) {
        $component_type = $spec['Component_Type'];
        $component_name = $spec['Component_Name'];
        
        echo '<input type="radio" name ="'.$component_type.'" value="'.$component_name.'" />'
        . $component_name . "<br><br>";
    }
}

//=================== REVIEW AN ORDER ========================

function print_session($db_handle){

    $total = 0;
    
    $components = $_SESSION['components'];
    
    //print chosen specs from the master list
    foreach($components as $component) {
        
        $SEL = "SELECT * FROM volktage_prep_db.Test_Component_List
                WHERE Component_Name = '$component'";
        $selected = mysql_query($SEL, $db_handle);
        
        while($chosen = mysql_fetch_assoc($selected)) {
            print "Your " . $chosen['Component_Type'] . " selection " . "is: ";
            print $chosen['Component_Name'] . "<br />";
            print "$" . $chosen['Cost'] . "<br />";
            print $chosen['ID'] . "<br /><br />";
            
            $total += $chosen['Cost'];
        }
    }
    print "Your total: $" . $total . "<br>";
    
    mysql_close($db_handle);
    
        sign_in_and_review();
        
    echo '<a href="#" onClick="history.back(); return false;">
          <input type="button" value="Edit">
          </a>';
    }

function print_order($order_name, $order_id, $db_handle) {

    $SUM = "SELECT * FROM $order_id";
    $summary = mysql_query($SUM, $db_handle)
    or die ("Query failed: " . mysql_error() . "<br>Actualy query: " . $SUM);
    
    if($summary) {
        echo "Summary for the order: <h3>'" . $_SESSION['order_name'] . "'</h3>";
    }
    $total = 0;
    while($component = mysql_fetch_assoc($summary)) {
        print $component['component_type']."<br>";
        print $component['component_name']."<br>";
        print "$".$component['cost']."<br>";
        print $component['id']."<br><br>";
        $total += $component['cost'];
    }
    print "Your total: $" . $total;
}

//===================== DELETE ORDER =======================

function delete_order_option($order_name) {
    
    echo '<form action="volktest_select_components.php" method="post">
            <input type="submit" name="delete" value ="Delete '.$order_name.'">
          </form>';
}

function delete_order ($order_id, $db_handle) {
    
    $SEL = "DELETE
            FROM volktageorders
            WHERE orderID = '$order_id'";
    $deleted_order = mysql_query($SEL, $db_handle)
    or die ("ERROR: " . mysql_error() . "<br>" . $SEL);
    
    if($deleted_order){
        echo "'" . $_SESSION['order_name'] . "' has been deleted.<br>";
    }
    
    $TBL = "DROP TABLE $order_id";
    $deleted_table = mysql_query($TBL, $db_handle)
    or die ("ERROR: " . mysql_error() . "<br>" . $TBL);
    
    if($deleted_table){
        echo "The table: '" . $order_id . "' has been deleted.<br>";
    }
}

//============================ OTHER HTML BUTTONS ==============================

function choices() {
# Replace Check Out action when integrate cart
?>

<br><br>
<div style = "float: left;">
    <form action="volktage_review_radios.php" method="post">
        <input type="submit" name="edit" value="Edit">
    </form>
    </div>

<div style = "float: left;">
    <form action="????" method="post">
        <input type="submit" value="Check Out">
    </form>
    </div>
<?php
}

function create_new() {
?>

   <a href="volktest_select_components.php">
    <input type="button" value="Create New Order">
   </a>
   
<?php
}



//====================== SIGN IN WITH JanRain API / SIGN OUT ===========================

function sign_out_button() {
    echo '<form action="volktage_sign_out_landing.php" method="post">
            <a href="#"><input type="submit" name="sign_out" value="Sign Out"></a>
          </form>';
}

function sign_in_and_review() {
    ?>
<head>
<script type="text/javascript">
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
    
    //Set the landing directory here:
    janrain.settings.tokenUrl = 'http://localhost/volktage_review_order.php';

    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
      window.attachEvent('onload', isReady);
    }

    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';

    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/volktage/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/volktage/engage.js';
    }

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();
</script>

</head>

<body>
    <a class="janrainEngage" href="#"><input type="button" value="Save"></a>
</body>

</html>
    <?php
}

function sign_in_from_customizer() {
    ?>
<head>
<script type="text/javascript">
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
    
    //Set the landing directory here:
    janrain.settings.tokenUrl = 'http://localhost/volktage_sign_in_landing.php';

    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
      window.attachEvent('onload', isReady);
    }

    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';

    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/volktage/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/volktage/engage.js';
    }

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();
</script>

</head>

<body>
    <a class="janrainEngage" href="#">
        <input type="submit" value="Sign-In"><br><br>
    </a>
</body>

</html>
    <?php
}

function test_sign_in() {
    ?>
<head>
<script type="text/javascript">
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
    
    //Set the landing directory here:
    janrain.settings.tokenUrl = 'http://localhost/volktage_test_landing.php';

    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
      window.attachEvent('onload', isReady);
    }

    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';

    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/volktage/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/volktage/engage.js';
    }

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();
</script>

</head>

<body>
    <a class="janrainEngage" href="#">
        <input type="button" value="Sign-In"><br><br>
    </a>
</body>

</html>
    <?php
}

//======================== END FUNCTIONS ==========================

?>
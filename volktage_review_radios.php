<?php
# volktage_review_radios.php
# Hub for modifying an order

session_start();

require_once 'volktage_db_connect.php';
require_once 'volktage_functions.php';

if(!isset($_SESSION['username'])){
    require_once 'volktage_get_session_vars.php';
}

$user_id = $_SESSION['id'];
echo "User id: " . $user_id . "<br>";

$order_id = '';

//======== Section 1: Assign Variable values to be used as function arguments =========

//============Rename if indicated=============

if(isset($_POST['new_name'])) {
    $order_id = $_SESSION['order_id'];
    $new_name = mysql_real_escape_string($_POST['new_name']);
    $_SESSION['order_name'] = $new_name;
    
    update_order_name($new_name, $order_id, $db_handle, $master_list);
}

//=========== Review selections if indicated ================
else if(isset($_POST['modify_order'])){
    
    $order_name = $_POST['modify_order'];
    $_SESSION['order_name'] = $order_name;
    echo "This order is named: " . $order_name . "<br>";
    
    $ID = "SELECT orderID
           FROM volktageorders
           WHERE orderName = '$order_name'";
    
    $got_id = mysql_query($ID, $db_handle);
    
    while($oid = mysql_fetch_array($got_id)){
        $order_id = $oid['orderID'];
    }
    echo "Order ID is: " . $order_id . "<br>";
    
}

//============== "Edit" clicked from 'Review Order' ==============
else if(isset($_POST['edit'])){
    $order_id = $_SESSION['order_id'];
}


//============ Create a new order ==============
else{ 
require_once 'volktage_get_session_vars.php';

assign_session_components();
$components = $_SESSION['components'];

new_user_if_needed($username, $user_id, $identifier, $db_handle);

# Add New Order to Order Table
$order_id = add_new_order_record($user_id, $db_handle);
$_SESSION['order_id'] = $order_id;
$_SESSION['order_name'] = $order_id;

# Create Table to House New Order
create_order_table ($order_id, $db_handle);
      
# Populate Order Table with Selections
populate_order_table ($order_id, $db_handle, $master_list);
}

//========= Section 2: Use values assigned in Section 1 as function arguments to display appropriate info ========

//==================== Display Existing Orders ===================

create_new();

delete_order_option($_SESSION['order_name']);

display_existing_builds($user_id, $db_handle);

echo "Session Order Name: <h3>'" . $_SESSION['order_name']. "'</h3><br>";
$_SESSION['order_id'] = $order_id;
echo "Session Order ID: " . $_SESSION['order_id'] . "<br><br>";

name_this_build();

# Dynamically generate radios for the form
echo '<form action="volktage_review_order.php" method = "post">';

generate_radios($db_handle, $master_list, $order_id);

echo '<input type="submit">
      </form>';

?>
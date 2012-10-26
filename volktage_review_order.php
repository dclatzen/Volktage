<?php
# volktage_review_order.php
# Displays a saved order for review before checkout

session_start();

require_once 'volktage_db_connect.php';
require_once 'volktage_functions.php';

$order_id = '';
$order_name = '';


//============ Update Order Table if coming from 'Review Radios' page =============
if ((isset($_POST['CPU'])) && (isset($_SESSION['order_id']))) {
   
   $order_id = $_SESSION['order_id'];
   $order_name = $_SESSION['order_name'];
   
   assign_session_components();
   populate_order_table ($order_id, $db_handle, $master_list);
}

//============ Create new order if from 'Select Components' or 'Print Session' pages ===============
else if (!isset($_SESSION['order_name'])) {
   
   # Add new user if coming from 'volktage_print_session.php'
   if(!isset($_SESSION['username'])){
      
   require 'volktage_get_session_vars.php';
   new_user_if_needed($username, $user_id, $identifier, $db_handle);
   }
   
   # Assign session components if coming from 'volktest_select_components.php'
   else if(!isset($_SESSION['components'])) {
   assign_session_components();
   }
   
   # For testing: confirm user id
   $user_id = $_SESSION['id'];
   
   $components = $_SESSION['components'];
   
   # Add New Order to Order Table
   $order_id = add_new_order_record($user_id, $db_handle);
   $_SESSION['order_id'] = $order_id;
   $_SESSION['order_name'] = $order_id;
   $order_name = $_SESSION['order_name'];
   
   # Create Table to House New Order
   create_order_table ($order_id, $db_handle);
         
   # Populate Order Table with Selections
   populate_order_table ($order_id, $db_handle, $master_list);
   
}

print_order($order_name, $order_id, $db_handle);
mysql_close($db_handle);
choices();

?>
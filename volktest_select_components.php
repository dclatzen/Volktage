<?php

session_start();
require 'volktage_db_connect.php';
require 'volktage_functions.php';

//======= Delete Previous Order if Indicated =========
# To improve: Using $_POST data like this probably violates REST principles.
# Should find a better way to delete orders.
if(isset($_POST['delete'])) {
    $order_id = $_SESSION['order_id'];
    delete_order ($order_id, $db_handle);
}

# Unset order-specific session variables
unset ($_SESSION['order_id']);
unset ($_SESSION['order_name']);
unset ($_SESSION['components']);

echo 'Create a new order: <br><br>';

//======== Form 'Submit' options are Log-in Dependent ========

# If user is logged in: 'Submit' jumps to 'Review Order' and the order is stored
if(isset($_SESSION['id'])){
    
    sign_out_button();
    
    $user_id = $_SESSION['id'];
    echo "Session id: " . $user_id;
    # Offer option to review an existing order
    echo "<br>Welcome, " . $_SESSION['username'] . ".<br>";
    display_existing_builds($user_id, $db_handle);
    
    echo '<form action="volktage_review_order.php" method = "post">';
}

# If user is not logged in, 'Submit' leads to a review page, but does not store the order
else {
    #Offer option to log in
    echo '<form action="volktage_print_session.php" method = "post">';
    sign_in_from_customizer();
}

generate_blank_radios($db_handle, $master_list);

echo '<input type="submit">
      </form>';

?>



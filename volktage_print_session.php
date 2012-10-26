<?php
# volktage_print_session.php
# gets info from volktest_select_components.php

session_start();   

require 'volktage_db_connect.php';
require 'volktage_functions.php';

unset ($_SESSION['order_id']);
unset ($_SESSION['order_name']);

assign_session_components();
print_session($db_handle);

?>






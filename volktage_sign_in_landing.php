<?php
# volktage_sign_in_landing.php
# Basic landing to acquire session id info

session_start();
require 'volktage_get_session_vars.php';

echo 'Thank you ' . $_SESSION['username'] . ', you have sucessfully signed in.<br><br>';
echo '<a href="volktest_select_components.php"><input type="button" value="Back to Customizer"></a>';

?>
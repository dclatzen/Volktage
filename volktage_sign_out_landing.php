<?php
#volktage_sing_out_landing.php

session_start();
session_destroy();
echo "You have successfully signed out.<br>";

echo '<a href="volktest_select_components.php">
    <input type="button" value="Back to Customizer">
    </a>';

?>


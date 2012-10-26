<?php
# volktage_sign_out_landing.php
# Landing page placeholder to destroy session. 

session_destroy();
echo "You have successfully signed out.<br>";

echo '<a href="volktest_select_components.php">
    <input type="button" value="Back to Customizer">
    </a>';

?>


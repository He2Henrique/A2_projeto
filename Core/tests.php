<?php
session_start(); // Start the session to access session variables
require_once ('core_func.php'); // Include the database connection file
require_once ('config_serv.php'); // Include the database connection file

echo '<div class="form-check form-switch">
  <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
  <label class="form-check-label" for="flexSwitchCheckDefault">Default switch checkbox</label>
</div>';
    
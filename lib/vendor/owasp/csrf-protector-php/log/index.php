<?php
/**
 * OWASP CSRF Protector Project
 * Code to redirect the user to previous directory
 * In case a user try to access this directory directly
 */
header('location: ../index.php');
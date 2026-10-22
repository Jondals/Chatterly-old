<?php
session_start();
header('Location: ' . (isset($_SESSION['usuario']) ? 'php/chatterly.php' : 'html/login.html'));

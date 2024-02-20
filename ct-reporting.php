<?php

/*

Plugin Name: Custom-Tin-Canny-Reporting-LearnDash-Plugin

Description: Custom reporting functionality for LearnDash courses.

Version: 1.0

Author: Vishal Soni

*/



// Include the main class file

require_once(plugin_dir_path(__FILE__) . 'classes/class-custom-reporting.php');



// Instantiate the main class

$custom_reporting = new Custom_Reporting();


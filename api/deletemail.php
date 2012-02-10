<?php
$email =  $_POST['email'];
require_once(__DIR__ . '/../../../../wp-config.php');
$eb = new TwentyfourEmailBin();
$eb->twentyfourEBdelete($email);

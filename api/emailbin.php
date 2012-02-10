<?php
$email =  $_POST['email'];
if( strlen($email) > 0 )
{
    require_once('../../../../wp-config.php');
    $eb = new TwentyfourEmailBin();
    $eb->twentyfourEBinsert($email);
}

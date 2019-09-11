<?php
if(array_key_exists('ip', $_GET)) {
        $html = file_get_contents("http://".$_GET['ip'].".ipaddress.com");//urldecode($_GET['url']));
        echo $html;
}
?>

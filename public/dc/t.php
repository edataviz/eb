<?php
include('db.php');
mysql_query("update params set `value`=now() where `key`='t'");
echo date('Y-m-d H:i:s');
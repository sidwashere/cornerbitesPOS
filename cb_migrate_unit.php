<?php
echo "DOCROOT=" . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "APP_ROOT=" . (isset($_SERVER['APP_ROOT'])?$_SERVER['APP_ROOT']:'unset') . "\n";

#!/usr/bin/env php
<?php

use Framework\Database\AbstractDatabase as DB;
use Framework\Util\Filesystem\File;

chdir(dirname(__FILE__));
$root = dirname(__FILE__)."/..";

require_once("$root/include/classes.php");

DB::dumpStructure(new File("migration/structure.sql"));
?>

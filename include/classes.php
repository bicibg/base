<?php

header("Content-Type: text/html; charset=UTF-8");
require_once("$root/Configuration/config.php");
require_once("$root/class/Framework/Util/GlobalArray.php");
require_once("$root/class/Configuration/Config.php");
require_once("$root/class/Framework/Database/Database.php");
require_once("$root/class/Framework/Database/AbstractDatabase.php");
require_once("$root/class/Framework/Database/AbstractDatabaseObject.php");
require_once("$root/class/Framework/Util/Command/Command.php");
require_once("$root/class/Framework/Util/Command/Diff.php");
require_once("$root/class/Framework/Util/Command/Output.php");
require_once("$root/class/Framework/Util/Command/Prompt.php");
require_once("$root/class/Framework/Util/Filesystem/AbstractFSNode.php");
require_once("$root/class/Framework/Util/Filesystem/File.php");
require_once("$root/class/Framework/Util/Filesystem/Directory.php");
require_once("$root/class/Framework/Util/TrObject.php");
require_once("$root/class/Framework/Util/Mailer/POP3.php");
require_once("$root/class/Framework/Util/Mailer/SMTP.php");
require_once("$root/class/Framework/Util/Mailer/phpmailerException.php");
require_once("$root/class/Framework/Util/Mailer/PHPMailer.php");


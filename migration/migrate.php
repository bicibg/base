#!/usr/bin/env php
<?php

error_reporting(0);

use Framework\Database\AbstractDatabase as DB;

chdir(dirname(__FILE__));
$root = dirname(__FILE__)."/..";

require_once("$root/include/classes.php");

function getFileList() {
	$list = [];
	foreach (glob("scripts/*-diff.sql") as $script) {
		$list[] = basename($script);
	}
	return $list;
}

function isApplied($status) {
	foreach ($status as $s) {
		if ($s['status'] == "success") return true;
	}
	return false;
}

function apply($index) {
	$files = getFileList();

	foreach ($files as $id => $file) {
		if ($id > $index) break;
		if (isApplied(getStatusOf($file))) continue;

		print("Fetching patch [$id] info from \"$file\"\n");
		$script = file_get_contents("scripts/$file");

		$status = "success";
		try {
			print("Try to apply patch...\n");
			/*DB::beginTransaction();
			DB::cliExec($script);
			DB::commit();
			*/
			DB::execute($script);
			print("Done.\n");
		} catch (Exception $e) {
			print("Error while applying patch [$id] :-(\n");
			$status = "error";
			print_r($e);
			DB::rollBack();
		}

		DB::execute("INSERT INTO `migration_scripts` (`name`, `status`) VALUES (?, ?)", [$file, $status]);
		if ($status == "error") {
			return false;
		}
	}
	return true;
}

function isFailed($status) {
	foreach ($status as $s) {
		if ($s['status'] == "error") return true;
	}
	return false;
}

function writeStatusShort($file) {
	$status = getStatusOf($file);

	if (isApplied($status)) {
		return "   ";
	} elseif (isFailed($status)) {
		return "[E]";
	} else {
		return "[N]";
	}
}

function getStatusOf($file) {
	$result = DB::query("
		SELECT      `id`,
					`executed`,
					`status`
		FROM		`migration_scripts`
		WHERE		`name` = ?
	", [$file]);
	return $result;
}

function tryCommand($command, $err_msg) {
	system($command, $retval);
	if ($retval) {
		throw new Exception($err_msg);
	}
}

function check($index) {
	$list = getFileList();
	$db_uu = sha1(uniqid());

	$retval = 0;

	try {

		print("Initializing database tmp_$db_uu from base...\n");
		tryCommand("echo \"CREATE DATABASE IF NOT EXISTS tmp_$db_uu\" | mysql -u root && mysql -u root tmp_$db_uu < base.sql", "Failed during initialization.");
		foreach ($list as $ind => $file_name) {
			if ($ind > $index) break;
			print("Applying patch [$ind] \"$file_name\".\n");
			tryCommand("mysql -u root tmp_$db_uu < scripts/$file_name", "Failed on patch [$ind] \"$file_name\".\n");
		}
		print("All patches applied.\n");
	} catch (Exception $e) {
		$retval = -1;
	} finally {
		print("Removing database tmp_$db_uu.\n");
		system("echo \"DROP DATABASE tmp_$db_uu\" | mysql -u root");
	}
	print("Done checking file. Got retval $retval\n");
	return $retval;
}


	$command = isset($argv[1]) ? $argv[1] : "help";


switch($command) {
	case "help":
		print("The following commands are available:\n");
		print("* init\n");
		print("\tInitializes the migration system\n");
		print("* list\n");
		print("\tLists change scripts\n");
		print("* applyAll\n");
		print("\tApplies all new ([N]) patches\n");
		print("* apply [list item number]\n");
		print("\tApplies/reapplies new/failed patch\n");

		break;

	case "init":
		print("Setting up migration system...\n");
		$script = file_get_contents("migration_scripts.sql");
		try {
			print("Getting Database ready for migrations...\n");
			DB::execute($script);
			print("Done.\n");
		} catch (Exception $e) {
			print("Error while setting up migrations :-(\n");
			$status = "error";
			print_r($e);
			DB::rollBack();
		}
		break;
	case "list":
		$list = getFileList();
		if (!count($list)) {
			print("No patches available");
			break;
		}
		$print_list = [];
		foreach ($list as $file_index => $file_name) {
			$print_list[] = "[$file_index]\t$file_name\t".writeStatusShort($file_name);
		}
		print(implode("\n", $print_list));
		break;
	case "apply":
		$list = getFileList();
		if (isset($argv[2]) && isset($list[$argv[2]])) {
			$file = $list[$argv[2]];
			if (isApplied(getStatusOf($file))) {
				print("File already applied. Aborting!");
				break;
			}

			if (apply($argv[2])) {
				print("Successfully applied patch(es).");
			}
		} else {
			print("No file with number \"$argv[2]\"");
		}
		break;
	case "applyAll":
		$list = getFileList();
		foreach ($list as $id => $file) {
			$status = getStatusOf($file);
			if (isApplied($status)) continue;
			if (!apply($id)) {
				print("Error while applying. Aborting :-(");
				break;
			}
		}
		print("Everything went according to plan :-).");
		break;
	default:
		print("Command \"$command\" not found!");
		break;
}

?>


#!/usr/local/zend/bin/php -q

<?php
//services/sync.php
//should be called by ./program.php, use chmod +x program.php
//script for syncing the Plifk files
//to be run at instance start-up and by crontab
//a script can be run everytime it tries to sync, it's name is 
$run_script = "run-script";
//or if it should be destroyed at the end:
$run_script_one_time = "run-script-once";
//and everytime it syncs:
$run_script_on_sync = "run-script-on-sync";

$rootdir = "/home/plifk";
$newdistribution = $rootdir . "/last-distribution-received";
$distribution_name = "distribution";
$release_name = "release.tar.gz";
$distribution = $rootdir . "/" . $distribution_name;
$release = $newdistribution . "/" . $release_name;

$last_release_md5 = @md5_file($release);

$output = shell_exec("s3cmd sync s3://plifk.admin/distribution-sync $newdistribution -f");

echo "Sync call finished.\n";

if(file_exists($newdistribution . "/" . $run_script))
{
	shell_exec("bash $run_script");
}

if(file_exists($newdistribution . "/" . $run_script_one_time))
{
	shell_exec("bash $newdistribution/$run_script_one_time");
	unlink("$newdistribution/$run_script_one_time");
}

echo "Scripts calls (if any) made.\n";

if(!$output || !file_exists($release) || ($last_release_md5 == @md5_file("$newdistribution/release.tar.gz")))
{
	echo "Nothing to do.\n";
	exit();
}

echo "New distribution received!\n";

$tar_output = array();
exec("tar xzvf $release -C $newdistribution", $tar_output, $tar_return_var);
if($tar_return_var != 0)
{
	exit(1);
}

echo "New distribution unpackaged at $newdistribution\n";

shell_exec("rm -rf $distribution/* && mv $newdistribution/$distribution_name/* $distribution");

echo "New distribution installed at $distribution\n";

if(!file_exists("$distribution-update-log"))
{
	shell_exec("echo \"#Last date is the current version being used\" >> $distribution-update-log");
}

shell_exec("echo `date` >> $distribution-update-log; rm $distribution-version; echo `date` >> $distribution-version");

if(file_exists($newdistribution . "/" . $run_script_on_sync))
{
	shell_exec("bash $run_script_on_sync");
}
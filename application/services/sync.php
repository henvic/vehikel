#!/usr/local/zend/bin/php -q

<?php
//services/sync.php
//should be called by ./program.php, use chmod +x program.php
//script for syncing the Plifk files
//to be run at instance start-up and by crontab
//a script can be run everytime it tries to sync, it's name is 
$runScript = "run-script";
//or if it should be destroyed at the end:
$runScriptOneTime = "run-script-once";
//and everytime it syncs:
$runScriptOnSync = "run-script-on-sync";

$rootdir = "/home/plifk";
$newdistribution = $rootdir . "/last-distribution-received";
$distributionName = "distribution";
$releaseName = "release.tar.gz";
$distribution = $rootdir . "/" . $distributionName;
$release = $newdistribution . "/" . $releaseName;

$lastReleaseHash = @md5_file($release);

$output = shell_exec("s3cmd sync s3://plifk.admin/distribution-sync " .
$newdistribution . " -f");

echo "Sync call finished.\n";

if (file_exists($newdistribution . "/" . $runScript)) {
    shell_exec("bash $runScript");
}

if (file_exists($newdistribution . "/" . $runScriptOneTime)) {
    shell_exec("bash $newdistribution/$runScriptOneTime");
    unlink("$newdistribution/$runScriptOneTime");
}

echo "Scripts calls (if any) made.\n";

if (! $output || ! file_exists($release) ||
 ($lastReleaseHash == @md5_file("$newdistribution/release.tar.gz"))) {
    echo "Nothing to do.\n";
    exit();
}

echo "New distribution received!\n";

$tarOutput = array();
exec("tar xzvf $release -C $newdistribution", $tarOutput, $tarReturnVar);
if ($tarReturnVar != 0) {
    exit(1);
}

echo "New distribution unpackaged at $newdistribution\n";

shell_exec("rm -rf $distribution/* && mv " .
"$newdistribution/$distributionName/* $distribution");

echo "New distribution installed at $distribution\n";

if (!file_exists("$distribution-update-log")) {
    shell_exec("echo \"#Last date is the current version being used\"" .
    " >> $distribution-update-log");
}

shell_exec("echo `date` >> $distribution-update-log;" .
" rm $distribution-version; echo `date` >> $distribution-version");

if (file_exists($newdistribution . "/" . $runScriptOnSync)) {
    shell_exec("bash $runScriptOnSync");
}
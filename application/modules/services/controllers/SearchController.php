<?php
class SearchController extends Ml_Controller_Action
{
    public function syncUserAction() {
        $config = array(
            'help|h' => 'prints this usage information',
            'action|a=s' => 'action name (default: index)',
            'controller|c=s' => 'controller name  (default: index)',
            'verbose|v' => 'explain what is being done',
            'uid=i' => 'User ID to sync the data from the DB to the search engine'
        );

        $options = new Ml_Console_Getopt($config);
        $options->setOption(Ml_Console_Getopt::CONFIG_PERMIT_UNKNOWN, true);
        $options->parse();

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $uid = $options->getOption("uid");

        if (! $uid) {
            echo $options->getUsageMessage();
            exit(0);
        }

        $result = $people->syncSearchById($uid);

        if ($result) {
            echo "Profile just synced, posts are going to be soon.\n";
            $exitCode = 0;
        } else {
            echo "Sync failed\n";
            $exitCode = 1;
        }

        exit($exitCode);
    }
}

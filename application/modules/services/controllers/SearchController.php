<?php
class SearchController extends Ml_Controller_Action
{
    public function syncAction() {
        $config = array(
            'help|h' => 'prints this usage information',
            'action|a=s' => 'action name (default: index)',
            'controller|c=s' => 'controller name  (default: index)',
            'verbose|v' => 'explain what is being done',
            'uid=i' => 'User ID',
            'username|u=s'      => 'Username (string)'
        );

        $options = new Ml_Console_Getopt($config);
        $options->setOption(Ml_Console_Getopt::CONFIG_PERMIT_UNKNOWN, true);
        $options->parse();

        $search =  $this->_registry->get("sc")->get("search");
        /** @var $search \Ml_Model_Search() */

        $username = $options->getOption("username");

        $uid = $options->getOption("uid");

        if (! $username && ! $uid) {
            echo $options->getUsageMessage();
            exit(0);
        }

        if ($uid) {
            $postsJobsSynced = $search->syncUserById($uid);
        } else {
            $postsJobsSynced = $search->syncUserByUsername($username);
        }

        if ($postsJobsSynced !== false) {
            echo $postsJobsSynced ,
            " posts jobs registered for search indexing / deleting for the user ",
                escapeshellcmd($username), "\n"
            ;
        } else {
            echo "Failed to snyc posts jobs for ";

            echo ($username) ? escapeshellcmd($username) : escapeshellcmd("uid " . $uid);

            echo "\n";
        }
    }

    public function syncAllAction() {
        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $search =  $this->_registry->get("sc")->get("search");
        /** @var $search \Ml_Model_Search() */

        $uids = $people->getUsersIds();

        $totalJobs = 0;

        echo "UID | username | posts search indexing / deleting jobs registered";

        foreach ($uids as $uid) {
            $userInfo = $people->getById($uid);

            if (! $userInfo) {
                echo "", escapeshellcmd($uid), " | user not found\n";
                continue;
            }

            $postsJobsSynced = $search->syncUser($userInfo);

            if ($postsJobsSynced !== false) {
                echo $uid, " | ", escapeshellcmd($userInfo["username"]), " | ", $postsJobsSynced, "\n";

                $totalJobs += $postsJobsSynced;
            } else {
                echo $uid, " | ", escapeshellcmd($userInfo["username"]), " | failure to sync\n";
                echo "Failed to snyc posts jobs for ", escapeshellcmd($userInfo["username"]), "\n";
            }
        }

        echo "A total of ", $totalJobs, " posts jobs were registered\n";
    }
}

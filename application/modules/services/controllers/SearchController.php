<?php
class SearchController extends Ml_Controller_Action
{
    public function rebuildUserAction() {
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

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $uid = $options->getOption("uid");

        if (! $uid) {
            echo $options->getUsageMessage();
            exit(0);
        }

        $result = $people->syncSearchById($uid, false);

        if (! $result) {
            echo "Profile sync failed\n";
            exit(1);
        }

        $syncPos = 0;
        $syncError = 0;

        $postsIds = $posts->getPostsIdsByUserId($uid);

        foreach ($postsIds as $postId) {
            $result = $posts->syncSearchById($postId);

            $syncPos += 1;
            if ($result === false) {
                $syncError += 1;
            }
        }

        if ($syncError) {
            echo "Profile synced. Sync failed for ", escapeshellcmd($syncError), "posts out of ", escapeshellcmd($syncPos), "\n";
            exit(1);
        }

        echo "Profile and ", escapeshellcmd($syncPos), " posts synced\n";
        exit(0);
    }
}

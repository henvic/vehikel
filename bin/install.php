#!/usr/bin/php -q
<?php
/**
 *
 * Helps with the install and setup of the application
 * See https://github.com/henvic/MediaLab/blob/master/README.md for more info
 *
 * @todo adopt composer or anything similar to make the install process better
 *
 */

class Install
{
    protected $_env = false;

    public $projectDirectory = "";

    public $colors;

    private function setValue ($prompt, &$value)
    {
        fwrite(STDOUT, "$prompt " . $this->colors->getColoredString("[" . $value . "]", "light_red") . " : ");
        $answer = mb_strcut(fgets(STDIN), 0, -1);

        if (! empty($answer)) {
            $value = $answer;
        }
    }

    private function execSimple($command)
    {
        exec($command, $output, $exitCode);

        if ($exitCode != 0) {
            fwrite(STDERR, "Error executing: " . $command . "\nExit code: " . $exitCode . "\n");
            exit(1);
        }

        return $exitCode;
    }

    /**
     * Get the path with regard to the project directory if inside it
     * or the absolute one, if not.
     *
     * @param  string $path
     * @return string $path
     */
    private function getPath($path)
    {
        $pos = strlen($this->projectDirectory . "/");
        if (substr($path, 0, $pos) == $this->projectDirectory . "/") {
            $path = substr($path, $pos);
        }

        return $path;
    }

    public function setEnvironment()
    {
        $env = array(
            "APPLICATION_ENV" => "production",
            "DEFAULT_TIMEZONE" => "GMT",
            "CACHE_PATH" => $this->projectDirectory . "/data/cache",
            "EXTERNAL_LIBRARY_PATH" => $this->projectDirectory . "/vendor",
            "APPLICATION_CONF_FILE" => $this->projectDirectory . "/application/configs/application.ini.dist",
        );

        // set environmental variables
        if (! $this->_env) {
            fwrite(STDOUT, "Configuration (to use the suggested value press return)\n");
            $this->setValue("Application environment?", $env['APPLICATION_ENV']);
            $this->setValue("Application Timezone?", $env['DEFAULT_TIMEZONE']);
            $this->setValue("Cache path?", $env['CACHE_PATH']);
            $this->setValue("PHP external library path?", $env['EXTERNAL_LIBRARY_PATH']);
            $this->setValue("Configuration file?", $env['APPLICATION_CONF_FILE']);
        } else {
            $env["APPLICATION_ENV"] = "testing";
        }

        return $env;
    }

    public function savePhpEnvironment($env)
    {
        $vendor = $this->getPath($env['EXTERNAL_LIBRARY_PATH']);
        $cache = $this->getPath($env['CACHE_PATH']);
        $conf = $this->getPath($env['APPLICATION_CONF_FILE']);

        $data = "<?php\n// Define application environment configuration\n" .
        "//This configuration file might be created by bin/install\n" .
        'define("APPLICATION_ENV", (defined("PHPUnit_MAIN_METHOD")) ? "testing" : "' .
        addslashes($env["APPLICATION_ENV"]) . '");' . "\n";

        $data .= 'define("EXTERNAL_LIBRARY_PATH", ';
        if (substr($vendor, 0, 1) != '/') {
            $data .= 'realpath(__DIR__ . "/../../' . addslashes($vendor) . '")';
        } else {
            $data .= '"' . addslashes($vendor) . '"';
        }
        $data .= ");\n";

        $data .= 'define("CACHE_PATH", ';
        if (substr($cache, 0, 1) != '/') {
            $data .= 'realpath(__DIR__ . "/../../' . addslashes($cache) . '")';
        } else {
            $data .= '"' . addslashes($cache) . '"';
        }
        $data .= ");\n";

        $data .=
        'define("APPLICATION_CONF_FILE", ';
        if (substr($conf, 0, 1) != '/') {
            $data .= 'realpath(__DIR__ . "/../../' . addslashes($conf) . '")';
        } else {
            $data .= '"' . addslashes($conf) . '"';
        }
        $data .= ");\n";

        $data .=
        'date_default_timezone_set("' . addslashes($env["DEFAULT_TIMEZONE"]) . '");' . "\n";

        file_put_contents(__DIR__ . "/../application/configs/Environment.php.dist", $data);

        fwrite(STDOUT, $this->colors->getColoredString("application/configs/Environment.php.dist set.\n",
            "light_green"));
    }

    public function copyConfig($file)
    {
        if (! file_exists($file)) {
            $q = "yes";

            if (! $this->_env) {
                $this->setValue("Copy the configuration model to the choosen application config path?", $q);
            }

            if ($q != "yes") {
                return false;
            }

            $this->execSimple("cp " . escapeshellarg("../application/configs/application.ini") . " " . escapeshellarg($file));

            fwrite(STDOUT, "Configuration file copied to " . escapeshellarg($file) . "\nDon't forget to edit it.\n");

            return true;
        }
    }

    public function checkExtensions()
    {
        $test = array(
            "memcached" => "essential for the sessions system and the security hashing mechanism",
            "mongo" => "can be ignored for now",
            "geoip" => "can be ignored",
        );

        $installed = get_loaded_extensions();

        $diff = array_diff(array_keys($test), $installed);

        if (empty($diff)) {
            fwrite(STDOUT, $this->colors->getColoredString("All required PHP extensions are installed.\n", "light_green"));

            return true;
        }

        fwrite(STDERR, $this->colors->getColoredString("The following PHP extensions are missing:\n", "light_red"));

        foreach ($diff as $each) {
            if (empty($test[$each])) {
                $info = " * " . $each . "\n";
            } else {
                $info = " * " . $each . " (" . $test[$each] . ")\n";
            }

            fwrite(STDERR, $this->colors->getColoredString($info, "red"));
        }

        if ($this->_env) {
            fwrite(STDERR, $this->colors->getColoredString("Not installing any extension for automatic installs at this time.\n", "yellow"));

            return false;
        }

        $value = "no";
        $this->setValue("Ignore and continue? ", $value);

        if ($value == "no") {
            exit(1);
        }

        return false;
    }

    public function installExtensionsForTravis($extensions)
    {
        $packages = array(
            "memcached",
            "mongo",
            "geoip"
        );

        //make sure we get updated channels
        $this->execSimple("sudo pecl channel-update pecl.php.net");

        foreach ($packages as $each) {
            $this->execSimple("sudo pecl install " . escapeshellarg($each));
            $this->execSimple("echo extension=" . escapeshellarg($each) . ".so >> /etc/php.ini");
        }

        fwrite(STDOUT, "PHP extensions installed on the Travis worker.\n");
    }

    public function __construct()
    {
        require __DIR__ . "/../library/Ml/Console/Colors.php";
        $this->colors = new Ml_Console_Colors();

        if (getenv("TRAVIS")) {
            $this->_env = "travis-ci";
        } elseif (in_array("auto", $_SERVER["argv"])) {
            $this->_env = "auto";
        }

        chdir( __DIR__ );

        fwrite(STDOUT, $this->colors->getColoredString("Welcome to the installation\n
Right now this script can:\n
- set the configs/Environment.php.dist values\n
- resolve the PHP library dependencies\n
- tell what PHP extensions are missing\n\n
Follow the instructions on:\nhttps://github.com/henvic/vehikel/blob/master/README.md\n\n", "cyan"));

        $this->projectDirectory = realpath(getcwd() . "/..");

        $this->checkExtensions();

        $env = $this->setEnvironment();

        $this->savePhpEnvironment($env);

        $this->copyConfig($env["APPLICATION_CONF_FILE"]);

        mkdir($env['CACHE_PATH'] . "/tests");

        fwrite(STDOUT, $this->colors->getColoredString("Finished installation.\n\n", "light_green"));
    }
}

new Install();

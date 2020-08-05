<?php

namespace Antares\Support\BaseCli;

use Antares\Support\Options\OptionsException;

abstract class BaseCliAction
{
    /**
     * Prototypes
     *
     * @var array
     */
    protected $prototypes;

    /**
     * Params options
     *
     * @var \Antares\Support\BaseCli\BaseCliOptions
     */
    protected $params;

    /**
     * Class construtor
     *
     * @param array $prototypes
     */
    public function __construct()
    {
        $this->params = new BaseCliOptions($this->prototypes);
    }

    /**
     * Show message error and finish action with errorlevel 1
     *
     * @param string $msg
     * @return void
     */
    public function showError($msg)
    {
        echo "\n";
        echo "ERROR: {$msg}\n";
        echo "\n";
        exit(1);
    }

    /**
     * Show message help and finish action
     *
     * @return void
     */
    public function showHelp()
    {
        echo $this->help();
        exit(0);
    }

    /**
     * Get help message
     *
     * @return string
     */
    abstract protected function help();

    /**
     * Run action
     *
     * @param array $params
     * @return bool
     */
    abstract public function run(array $params);

    /**
     * Create and execute command
     *
     * @param array $cmd
     * @return void
     */
    public static function exec(array $cmd)
    {
        $cli = new static();

        $ex = null;
        try {
            $cli->run($cmd);
        } catch (OptionsException $e) {
            $ex = $e;
        } catch (BaseCliException $e) {
            $ex = $e;
        }

        if (!empty($ex)) {
            if ($cli->params->has('help')) {
                $cli->showHelp();
            }
            $cli->showError($ex->getMessage());
        }
    }
}

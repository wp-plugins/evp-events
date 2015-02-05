<?php
/**
 * Checks System Environment for correct plugin setup
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketMaintenanceBundle\Services;
use Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

umask(0000);
/**
 * Class SystemEnvironment
 */
class SystemEnvironment {

    const CONSOLE = '/console';
    const INSTALL_DDL = '/tickets/install/ddl/*';
    const INSTALL_DML = '/tickets/install/dml/*';
    const UNINSTALL_SQL = '/tickets/uninstall/*';

    const DUMP_LEVEL = 2;
    const PARAMS_YML = '/config/parameters.yml';

    /**
     * @var array
     */
    private $chmodDirs = array(
        '/config',
        '/logs',
        '/cache',
        '/data',
    );

    /**
     * @var array
     */
    private $chmodFiles = array(
        '/config/parameters.yml',
    );

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var object
     */
    private $foreignDbHandler;

    /**
     * @var CacheClearCommand
     */
    private $cacheClearCommand;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var Application
     */
    private $application;

    /**
     * Sets the RootDir aka kernel.root_dir
     *
     * @param string $rootDir
     * @return self
     */
    public function setRootDir($rootDir) {
        $this->rootDir = $rootDir;
        return $this;
    }

    /**
     * @param CacheClearCommand $clearer
     */
    public function setCacheClearer(CacheClearCommand $clearer)
    {
        $this->cacheClearCommand = $clearer;
    }

    /**
     * @param $params
     */
    public function setParameters($params) {
        $this->cacheDir = $params['cache_dir'];
    }

    /**
     * Clears the Symfony cache and, optionally, warms-up
     *
     * @param bool $warmup
     */
    public function clearCache($warmup = true) {
        $noWarm = $warmup ? '' : 'no-warmup';
        $input = new ArgvInput(array('argv' => $noWarm));
        $output = new ConsoleOutput;

        $this->cacheClearCommand->run($input, $output);
    }

    /**
     * Tries to enable the plugin
     *
     * @return self
     */
    public function enablePlugin()
    {
        $this->chmodDirectories();
        $this->chmodFiles();
        $this->updateConfig();

        $this->launchConsoleApp();

        $this->initDbSchema();
        $this->runInstallQueries();
        $this->patchRobotsTxt();

        return $this;
    }

    private function runInstallQueries()
    {
        $path = $this->rootDir . '/../tickets/install/';
        $files = new \GlobIterator($path . '*.sql');

        $connection = $this->application->getKernel()->getContainer()->get('database_connection');

        /** @var \SplFileInfo $fileInfo */
        foreach ($files as $fileInfo) {
            $file = new \SplFileObject($path . $fileInfo->getFilename());

            $sql = '';
            while (!$file->eof()) {
                $sql .= $file->fgets();
            }

            $connection->executeQuery($sql);
        }
    }

    private function launchConsoleApp()
    {
        require ($this->rootDir . '/bootstrap.php.cache');
        require ($this->rootDir . '/AppKernel.php');

        $kernel = new \AppKernel('prod', true);
        $app = new Application($kernel);
        $app->setAutoExit(false);
        $app->setCatchExceptions(false);

        $this->application = $app;
    }

    /**
     * Adds initial data to Db
     *
     * @return string
     */
    public function addInitialData() {
        $output = '';
        $files = glob($this->rootDir .'/..' .self::INSTALL_DML);
        foreach ($files as $sqlFile) {
            $query = file_get_contents($sqlFile);
            $output .= "\n" .$this->foreignDbHandler->query($query);
        }
        return $output;
    }

    /**
     * Edits the robots.txt file for disallow some URLs
     */
    public function patchRobotsTxt()
    {
        $robotsFile = __DIR__ . '/../../../../../../../../robots.txt';
        $toAdd = '';
        if (file_exists($robotsFile)) {
            $toAdd = "Disallow: /evp-tickets/*/send\nDisallow: /evp-tickets/*/print\n";
        } else {
            $toAdd = "User-agent: *\nDisallow: /evp-tickets/*/send\nDisallow: /evp-tickets/*/print\n";
        }
        @file_put_contents($robotsFile, $toAdd, FILE_APPEND);
    }

    /**
     * Tries to chmod necessary directories
     */
    private function chmodDirectories()
    {
       foreach ($this->chmodDirs as $dir) {
           @chmod($this->rootDir . $dir, 0777);
       }
    }

    /**
     * Tries to chmod necessary files
     */
    private function chmodFiles()
    {
        foreach ($this->chmodFiles as $file) {
            @chmod($this->rootDir . $file, 0666);
        }
    }

    /**
     * Updates some settings in parameters.yml file
     */
    private function updateConfig()
    {
        $params = $this->loadParametersFromYaml();
        $params['secret'] = uniqid(md5(rand()));
        $params['pdf_converter'] = 'over_http';

        $this->dumpModifiedParamsAsYaml($params);
    }

    /**
     * Initializes Database schema
     *
     * @return string
     */
    private function initDbSchema()
    {
        $this->application->run(
            new ArrayInput(array(
                    'command' => 'doctrine:schema:update',
                    '--force' => true,
                    '--quiet' => true,
                ))
        );
    }

    /**
     * Load the parameters from the parameters.yml file
     * @author Dmitrijus Glezeris <d.glezeris@evp.lt>
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @return array
     */
    private function loadParametersFromYaml() {
        $content = file_get_contents($this->rootDir .self::PARAMS_YML);

        if (empty($content)) {
            throw new ResourceNotFoundException('File not found or not readable ' .$this->rootDir .self::PARAMS_YML);
        }
        $yaml = new Parser;
        $parsedContent = $yaml->parse($content, false, true);
        $parameters = $parsedContent['parameters'];
        return $parameters;
    }

    /**
     * Dumps the modified parameters into parameter.yml
     * @author Dmitrijus Glezeris <d.glezeris@evp.lt>
     *
     * @param $newParameters
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function dumpModifiedParamsAsYaml($newParameters)
    {
        if (!array_key_exists('parameters', $newParameters)) {
            $newParameters = array('parameters' => $newParameters);
        }

        $dumper = new Dumper;
        $newParametersAsYaml = $dumper->dump($newParameters, self::DUMP_LEVEL);
        file_put_contents($this->rootDir .self::PARAMS_YML, $newParametersAsYaml);
    }
}

<?php

use Crontab\Crontab;
use Crontab\CrontabFileHandler;

/**
 * CrontabFileHandlerTest
 *
 * @author Jacob Kiers <jacob@alphacomm.nl>
 */
class CrontabFileHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Crontab
     */
    private $crontab;

    /**
     * @var CrontabFileHandler
     */
    private $crontabFileHandler;

    /**
     * @var string
     */
    private $fixturesDir;

    public function setUp()
    {
        $this->fixturesDir = __DIR__.'/../../fixtures';
        $this->crontabFileHandler = new CrontabFileHandler();
        $this->crontab = new Crontab();
    }

    public function testParseFromFile()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixturesDir.'/crontab');
        $this->assertCount(2, $this->crontab->getJobs());

        $jobs = $this->crontab->getJobs();
        $job1 = array_shift($jobs);
        $job2 = array_shift($jobs);
        $this->assertEquals('cmd', $job1->getCommand());
        $this->assertEquals('cmd2', $job2->getCommand());
    }

    public function testWriteToFileIsSuccessfulWhenFileIsWritable()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixturesDir.'/crontab');

        $file = tempnam(sys_get_temp_dir(), 'cron');
        $this->crontabFileHandler->writeToFile($this->crontab, $file);

        $this->assertSame($this->crontab->render().PHP_EOL, file_get_contents($file));
        unlink($file);
    }

    public function testWriteToFileThrowsExceptionWhenFileIsNotWritable()
    {
        $fail = true;
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixturesDir.'/crontab');

        $file = tempnam(sys_get_temp_dir(), 'cron');
        touch($file);
        chmod($file, 0400);

        try {
            // We cannot use @expectedException annotation,
            // because we have to remove the temp file afterwards.
            $this->crontabFileHandler->writeToFile($this->crontab, $file);
        } catch(\InvalidArgumentException $e) {
            $fail = false;
        }

        chmod($file, 0600);
        unlink($file);

        if ($fail) {
            $this->fail('Expected an InvalidArgumentException because the file is not writable.');
        }
    }
}

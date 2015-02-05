<?php

namespace Evp\Bundle\TicketMaintenanceBundle\Services;
use Crontab\Job;
/**
 * Class CronJobFactory
 * @package Evp\Bundle\TicketMaintenanceBundle\Services
 *
 * Factory class for the cron job
 */
class CronJobFactory {
    /**
     * @var string
     */
    protected $cronInterval;
    /**
     * @var string
     */
    protected $cronCommand;

    /**
     * @param $options
     */
    function __construct($options)
    {
        $this->cronInterval = $options['expire_interval_in_minutes'];
        $this->cronCommand = $options['command_to_execute'];
    }

    public function createJob() {
        $job = new Job();
        $job->setMinute($this->cronInterval )
            ->setCommand($this->cronCommand)
        ;

        return $job;
    }
} 
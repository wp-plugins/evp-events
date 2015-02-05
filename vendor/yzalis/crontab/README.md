Crontab Component
=================

[![Build Status](https://secure.travis-ci.org/yzalis/crontab.png?branch=master)](http://travis-ci.org/yzalis/Crontab)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/fa7a5efd-f97d-4b0a-8c62-5610d83904c6/small.png)](https://insight.sensiolabs.com/projects/fa7a5efd-f97d-4b0a-8c62-5610d83904c6)

Crontab provide a php 5.3 lib to create crontab file.

	use Crontab\Crontab;
	use Crontab\Job;

	$job = new Job();
	$job
		->setMinute('*/5')
		->setHour('*')
		->setDayOfMonth('*')
		->setMonth('1,6')
		->setDayOfWeek('*')
		->setCommand('myAmazingCommandToRunPeriodically')
	;

	$crontab = new Crontab();
	$crontab->addJob($job);
	$crontab->write();

You can render what you have created:

	echo $crontab->render();

And then you can delete a job you don't want anymore:

	$crontab->removeJob($theJobYouWantToDelete);

When you create a Crontab, it will automatically parse your current crontab file and add all present job into your new object.

Resources
---------

You can run the unit tests with the following command. You need to be in the crontab directory and have phpunit installed on your computer:

    phpunit -v

<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Codeages\Biz\Framework\Queue\Worker;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Fixtures\QueueJob\ExampleFailedJob;
use Tests\Fixtures\QueueJob\ExampleFailedRetryJob;
use Tests\Fixtures\QueueJob\ExampleTimeoutJob;

class WorkerTest extends QueueBaseTestCase
{
    public function testRun_FinishedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo("ExampleFinishedJob executed."));
        $this->assertNotInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE));
    }

    public function testRun_FailedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFailedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo("ExampleFailedJob executed."));
        $this->assertNotInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE));
        $this->assertInDatabase('biz_queue_failed_job', array(
            'queue' => self::TEST_QUEUE,
            'reason' => 'ExampleFailedJob execute failed.'
        ));
    }

    public function testRun_FailedRetryJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job');
        $job = new ExampleFailedRetryJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo("ExampleFailedRetryJob executed."));
        $this->assertNotInDatabase($queueOptions['table'], array(
            'queue' => self::TEST_QUEUE,
            'executions' => 1,
            'reserved_time' => 0,
            'expired_time' => 0,
        ));
    }
}
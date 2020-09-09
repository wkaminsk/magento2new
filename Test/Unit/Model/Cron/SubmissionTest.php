<?php

namespace Riskified\Decider\Test\Unit\Model\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Riskified\Decider\Model\Api\Config as ApiConfig;
use Riskified\Decider\Model\Cron\Submission;
use Riskified\Decider\Model\Queue;

class SubmissionTest extends \PHPUnit\Framework\TestCase
{
    private $apiConfig;
    private $queue;
    public function setUp()
    {
        $this->apiConfig = $this->getMockBuilder(ApiConfig::class)
            ->setMethods(['isEnabled'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->queue = $objectManager->getObject(Queue::class);
    }

    /**
     * Execute.
     */
    public function testExecute()
    {
        $this->apiConfig->method("isEnabled");

        $retries = $this->queue
            ->getCollection()
            ->addFieldToFilter(
                'attempts',
                [
                    ['lt' => Submission::MAX_ATTEMPTS]
                ]
            );
    }
}

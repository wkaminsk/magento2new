<?php

namespace Riskified\Decider\Test\UnitController\Response;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Riskified\Decider\Model\Api\Api;
use Riskified\DecisionNotification\Model\Notification;


class GetTest extends \PHPUnit\Framework\TestCase
{
    private $request;
    /**
     * @var ResponseInterface|MockObject
     */
    private $response;
    private $apiMock;


    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect', 'representJson', 'setHttpResponseCode'])
            ->getMockForAbstractClass();

        $this->apiMock = $this->getMockBuilder(Api::class)
            ->setMethods(['initSdk', 'parseRequest'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->apiMock
            ->method('parseRequest')
            ->with($this->request)
            ->willReturn('82262e49b924bb85cc290f6190aa123895a6f351f0a7239e39bb9a3dbf5c0eee');
    }

    public function testExecute()
    {
        $jsonRequest = '{"username":"customer@example.com", "password":"password"}';
        $loginSuccessResponse = '{"order": 123, "description":"Order-Update event triggered."}';
        $this->withRequest($jsonRequest);
        $this->apiMock->method('initSdk');

        self::assertEquals($loginSuccessResponse, $loginSuccessResponse);
    }

    /**
     * Emulates request behavior.
     *
     * @param string $jsonRequest
     */
    private function withRequest(string $jsonRequest): void
    {
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');
    }

}

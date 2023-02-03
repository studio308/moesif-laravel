<?php namespace JonnyPickett\MoesifLaravel\Tests;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JonnyPickett\MoesifLaravel\Middleware\Moesif;
use JonnyPickett\MoesifLaravel\Sender\MoesifApi;

class MoesifLaravelTest extends MoesifLaravelTestCase
{
    /** @var \Mockery\MockInterface $moesifApiMock */
    private $moesifApiMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->moesifApiMock = $this->mock(MoesifApi::class);
    }

    public function testMoesifMiddlewareIsRegistered()
    {
        self::assertEquals([
            'moesif' => Moesif::class,
        ], $this->app['router']->getMiddleware());
    }

    public function testTrackIsCalled()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $this->app['config']->set('moesif.application_id', 'test_id');

        $expectedRequest = new Request();
        $expectedResponse = new Response();

        $expectedData = [
            'request' => [
                'time' => $now->format(Moesif::DATE_TIME_FORMAT),
                'verb' => $expectedRequest->method(),
                'uri' => $expectedRequest->fullUrl(),
                'ip_address' => $expectedRequest->ip(),
                'headers' => [],
                'body' => $expectedRequest->input(),
            ],
            'response' => [
                'time' => $now->format(Moesif::DATE_TIME_FORMAT),
                'status' => $expectedResponse->status(),
                'headers' => [
                    'cache-control' => $expectedResponse->headers->get('cache-control'),
                    'date' => $expectedResponse->headers->get('date'),
                ],
            ],
        ];

        $this->moesifApiMock->shouldReceive('track')->with($expectedData);

        $actualResponse = (app()->make(Moesif::class))->handle($expectedRequest, function () use ($expectedResponse) {
            return $expectedResponse;
        });

        self::assertEquals($expectedResponse, $actualResponse);
    }
}

<?php namespace Studio308\MoesifLaravel\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Studio308\MoesifLaravel\Sender\MoesifApi;

class Moesif
{
    public const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * @var MoesifApi
     */
    private $moesifApi;

    public function __construct(MoesifApi $moesifApi)
    {
        $this->moesifApi = $moesifApi;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // do action before response
        $start = Carbon::now()->timezone('UTC');

        // Handle on passed down request
        $response = $next($request);

        $apiVersion = config('moesif.apiVersion');
        $maskRequestHeaders = config('moesif.maskRequestHeaders');
        $maskRequestBody = config('moesif.maskRequestBody');
        $maskResponseHeaders = config('moesif.maskResponseHeaders');
        $maskResponseBody = config('moesif.maskResponseBody');
        $identifyUserId = config('moesif.identifyUserId');
        $identifySessionId = config('moesif.identifySessionId');
        $getMetadata = config('moesif.getMetadata');
        $skip = config('moesif.skip');
        $debug = config('moesif.debug', false);

        // if skip is defined, invoke skip function.
        if (!is_null($skip)) {
            if ($skip($request, $response)) {
                if ($debug) {
                    Log::info('[Moesif] : skip function returned true, so skipping this event.');
                }
                return $response;
            }
        }

        $requestData = [
            'time' => $start->format(self::DATE_TIME_FORMAT),
            'verb' => $request->method(),
            'uri' => $request->fullUrl(),
            'ip_address' => $request->ip(),
        ];
        if (!is_null($apiVersion)) {
            $requestData['api_version'] = $apiVersion($request, $response);
        }
        $requestHeaders = [];
        foreach ($request->headers->keys() as $key) {
            $requestHeaders[$key] = (string) $request->headers->get($key);
        }
        if (!is_null($maskRequestHeaders)) {
            $requestData['headers'] = $maskRequestHeaders($requestHeaders);
        } else {
            $requestData['headers'] = $requestHeaders;
        }
        $requestContent = $request->getContent();
        if (!is_null($requestContent)) {
            $requestBody = $request->input();
            if (is_null($requestBody)) {
                if ($debug) {
                    Log::info('[Moesif] : request body not be empty and not json, base 64 encode');
                }
                $requestData['body'] = base64_encode($requestContent);
                $requestData['transfer_encoding'] = 'base64';
            } else {
                if (!is_null($maskRequestBody)) {
                    $requestData['body'] = $maskRequestBody($requestBody);
                } else {
                    $requestData['body'] = $requestBody;
                }
            }
        }
        $end = Carbon::now()->timezone('UTC');
        $responseData = [
            'time' => $end->format(self::DATE_TIME_FORMAT),
            'status' => $response->getStatusCode(),
        ];
        $responseContent = $response->getContent();
        if (!is_null($responseContent)) {
            $jsonBody = json_decode($response->getContent(), true);
            if (!is_null($jsonBody)) {
                if (!is_null($maskResponseBody)) {
                    $responseData['body'] = $maskResponseBody($jsonBody);
                } else {
                    $responseData['body'] = $jsonBody;
                }
            } else {
                if (!empty($responseContent)) {
                    $responseData['body'] = base64_encode($responseContent);
                    $responseData['transfer_encoding'] = 'base64';
                }
            }
        }
        $responseHeaders = [];
        foreach ($response->headers->keys() as $key) {
            $responseHeaders[$key] = (string) $response->headers->get($key);
        }
        if (!is_null($maskResponseHeaders)) {
            $responseData['headers'] = $maskResponseHeaders($responseHeaders);
        } else {
            $responseData['headers'] = $responseHeaders;
        }
        $data = [
            'request' => $requestData,
            'response' => $responseData,
        ];
        if (!is_null($identifyUserId)) {
            $data['user_id'] = $this->ensureString($identifyUserId($request, $response));
        }
        if (!is_null($identifySessionId)) {
            $data['session_token'] = $this->ensureString($identifySessionId($request, $response));
        } elseif ($request->hasSession()) {
            $data['session_token'] = $this->ensureString($request->session()->getId());
        }
        if (!is_null($getMetadata)) {
            $data['metadata'] = $getMetadata($request, $response);
        }

        $this->moesifApi->track($data);

        return $response;
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function ensureString($item): ?string
    {
        if (is_null($item)) {
            return $item;
        }
        if (is_string($item)) {
            return $item;
        }
        return strval($item);
    }
}

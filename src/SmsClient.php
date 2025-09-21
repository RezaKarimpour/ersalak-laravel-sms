<?php

namespace Ersalak\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsClient
{
    private string $username;
    private string $password;
    private string $baseUrl;
    private Client $client;
    private bool $logger;

    const SUCCESS_CODE           = 200;
    const BAD_REQUEST_ERROR      = "bad request error.";
    const INVALID_RESPONSE_ERROR = "invalid response error.";

    public function __construct(string $username, string $password, string $baseUrl, bool $logger = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl  = rtrim($baseUrl, '/');
        $this->client   = new Client();
        $this->logger   = $logger;
    }

    private function sendRequest(string $endpoint, array $payload): array
    {
        $url = "{$this->baseUrl}{$endpoint}";

        // اضافه کردن Username و Password به payload
        $payload = array_merge([
            'username' => $this->username,
            'password' => $this->password,
        ], $payload);

        // آماده‌سازی برای multipart/form-data
        $multipart = [];
        foreach ($payload as $name => $value) {
            $multipart[] = [
                'name'     => $name,
                'contents' => $value,
            ];
        }

        try {
            $response = $this->client->post($url, [
                'multipart' => $multipart,
            ]);

            $body = $response->getBody()->getContents();

            if ($this->logger) {
                Log::info("SMS API response", [
                    'endpoint' => $endpoint,
                    'body'     => $body,
                ]);
            }

            return $this->processResponse(json_decode($body, true));

        } catch (RequestException $e) {
            $responseBody = $e->hasResponse()
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : null;

            if ($this->logger) {
                Log::error("HTTP RequestException", [
                    'endpoint'  => $endpoint,
                    'error'     => $e->getMessage(),
                    'http-code' => $e->getCode(),
                    'response'  => $responseBody,
                ]);
            }

            if ($responseBody) {
                return $this->processResponse($responseBody);
            }

            throw new \RuntimeException("HTTP error: " . $e->getMessage(), $e->getCode(), $e);

        } catch (ConnectException $e) {
            if ($this->logger) {
                Log::critical("Connection error", [
                    'endpoint' => $endpoint,
                    'error'    => $e->getMessage(),
                ]);
            }

            throw new \RuntimeException("Connection failed: " . $e->getMessage(), $e->getCode(), $e);

        } catch (GuzzleException $e) {
            if ($this->logger) {
                Log::error("Unhandled GuzzleException", [
                    'endpoint' => $endpoint,
                    'error'    => $e->getMessage(),
                ]);
            }

            throw new \RuntimeException("Guzzle error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sendSms(string $source, string $destination, string $message, int $send_to_black_list = 1): array
    {
        return $this->sendRequest('/webservice/v2rest/sendsms', [
            'source'             => $source,
            'message'            => $message,
            'destination'        => $destination,
            'send_to_black_list' => $send_to_black_list,
        ]);
    }

    public function p2p(array $source, array $destination, array $message, array $send_to_black_list): array
    {
        $data = [];
        foreach ($source as $i => $src) {
            $data[] = [
                'source'             => $src,
                'destination'        => $destination[$i] ?? '',
                'message'            => $message[$i] ?? '',
                'send_to_black_list' => $send_to_black_list[$i] ?? false,
            ];
        }

        return $this->sendRequest('/webservice/v2rest/p2psendsms', [
            'data' => json_encode($data), // بعضی APIها نیاز به JSON برای P2P دارند
        ]);
    }

    public function template(int $template_id, array $parameters, string $destination, int $send_to_black_list = 1): array
    {
        return $this->sendRequest('/webservice/v2rest/template', [
            'template_id'        => $template_id,
            'parameters'         => json_encode($parameters),
            'destination'        => $destination,
            'send_to_black_list' => $send_to_black_list,
        ]);
    }

    public function msgStatus(string|array $msg_ids): array
    {
        return $this->sendRequest('/webservice/v2rest/msgstatus', [
            'msgid' => is_array($msg_ids) ? implode(',', $msg_ids) : $msg_ids,
        ]);
    }

    public function getCredit(): array
    {
        return $this->sendRequest('/webservice/v2rest/getcredit', []);
    }

    public function getRialCredit(): array
    {
        return $this->sendRequest('/webservice/v2rest/getcredit', []);
    }

    public function getTemplates(): array
    {
        return $this->sendRequest('/webservice/v2rest/templatelist', []);
    }

    private function processResponse(array $response): array
    {
        if (!isset($response['meta']['status'])) {
            throw new \RuntimeException(self::INVALID_RESPONSE_ERROR, 409);
        }

        $status = $response['meta']['status'];
        if ($status === self::SUCCESS_CODE) {
            return (array) ($response['data'] ?? []);
        }

        $code         = is_numeric($status) ? (int) $status : 400;
        $errorMessage = !empty($response['meta']['message']) ? $response['meta']['message'] : self::BAD_REQUEST_ERROR;

        throw new \RuntimeException($errorMessage, $code);
    }
}

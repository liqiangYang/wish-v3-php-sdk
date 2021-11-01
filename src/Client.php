<?php

namespace Wish;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Wish oAuth client class.
 *
 */
class Client
{

    const URL = "https://merchant.wish.com/v3";

    /**
     * @var string
     */
    protected $client_id;

    protected $secret;

    protected $access_token;

    /**
     * @var array
     */
    protected $request_headers = [];

    /**
     * Create a new instance of Client.
     *
     * @param string $client_id
     * @return void
     */
    public function __construct(string $client_id, string $secret)
    {
        if (is_null($client_id) || !trim($client_id)) {
            throw new \Exception("No client ID found. A valid client ID is required.");
        }
        $this->client_id = $client_id;
        $this->secret = $secret;
    }

    /**
     * Create a new instance of GuzzleHttp Client.
     *
     * @return GuzzleClient
     */
    public function createHttpClient()
    {
        return new GuzzleClient();
    }

    public function formatQuery($params) {
        return http_build_query($params);
    }

    public function auth()
    {
        $uri = "/oauth/authorize";
        return sprintf("%s%s?%s", self::URL, $uri, $this->formatQuery(['client_id' => $this->client_id]));
    }

    public function accessToken($code)
    {
        $uri = "/oauth/access_token";
        $query = $this->formatQuery([
            'client_id' => $this->client_id,
            'client_secret' => $this->secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://www.baidu.com',
        ]);
        return sprintf("%s%s?%s", self::URL, $uri, $query);
    }

    public function refreshAccessToken($refresh_token)
    {
        $uri = "/oauth/refresh_token";
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];
        return $this->request('get', $uri, $params, [], []);
    }

    private function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }

    public function get($uri, $params = []) {
        return $this->request('get', $uri, $params);
    }

    public function put($uri, $params = [], $data = []) {
        return $this->request('put', $uri, $params, $data);
    }

    public function post($uri, $params = [], $data = []) {
        return $this->request('post', $uri, $params, $data);
    }

    public function delete($uri, $params = []) {
        return $this->request('delete', $uri, $params);
    }

    public function request($method, $uri, $params = [], $data = [], $headers = false)
    {
        if ($headers === false) {
            $headers = [
                'authorization' => "Bearer {$this->access_token}"
            ];
        }

        $guzzleClient = $this->createHttpClient();
        $uri = self::URL . $uri;
        if (!empty($params)) {
            $uri = sprintf("%s?%s", $uri, $this->formatQuery($params));
        }
        $respose = $guzzleClient->request($method, $uri, ['body' => $data, 'headers' => $headers]);
        return json_decode($respose->getBody(), true);
    }
}

<?php

/**
 * Author: Git6.com
 * Site: https://git6.com
 *
 * Require plugins
 * - WP REST API
 * - JWT Authentication for WP-API
 * optional: ACF to REST API
 */
class wprestapi
{

    public $token = 'TOKEN'; // JWT Authentication for WP-APIを用いてトークンを発行
    public $site_url = 'https://git6.com'; // サイトのURL
    public $endpoint_base = '/wp-json/wp/v2'; // APIエンドポイント

    public $last_response_header;

    /* 呼び出す時にtokenを設定する事も出来る */
    public function __construct($token = false)
    {
        if ($token) {
            $this->token = $token;
        }
    }

    /* tokenを設定するならこれを呼べばいい */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /* 毎回endpointのbaseを設定するならこれを呼べばいい */
    public function setEndpoint($endpoint_base)
    {
        $this->endpoint_base = $endpoint_base;
    }


    /* Common */
    public function sendRequest($method, $endpoint, $parm = false)
    {
        if ($parm) {
            $content = $parm;
        } else {
            $content = array();
        }
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($content)),
            'Authorization: Bearer ' . $this->token
        );

        $context = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $header),
                'content' => json_encode($content),
                'ignore_errors' => true
            )
        );

        $query_strings = $this->site_url . $this->endpoint_base . $endpoint;

        $result_data = file_get_contents($query_strings, false, stream_context_create($context));
        if ($result_data) {
            $this->last_response_header = $http_response_header;
        } else {
            //$http_response_headerはリクエストに失敗したら更新しないらしい
            $this->last_response_header = '';
            return json_encode(array('error' => 'ChatworkAPI:Wrapper:sendRequest:file_get_contents'));
        }

        return json_decode($result_data);
    }

    /* レスポンスヘッダから特定の項目の値を抽出 */
    public function getResponseHeader($header_item_name)
    {
        foreach ($this->last_response_header as $key => $r) {
            if (stripos($r, $header_item_name) !== FALSE) {
                list($headername, $headervalue) = explode(":", $r, 2);
                return trim($headervalue);
            }
        }
    }

    /* -------------------------------------------- */
    /* Test */
    /* -------------------------------------------- */

    public function testPost()
    {
        $endpoint = '/posts';

        $time = new DateTime("now");
        $article['title'] = 'テストタイトル';
        $article['content'] = 'コンテンツの中身';
        $article['status'] = 'draft';
        $article['categories'] = '1';
        $send_data_body = $article;

        return $this->sendRequest('POST', $endpoint, $send_data_body);
    }

    /* -------------------------------------------- */
}

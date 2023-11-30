<?php

namespace Parisprobr\PhpConfluence;

use GuzzleHttp\Client;

class ConfluenceIntegration
{
    const BASE_URL = 'http://confluence.local/rest/api/content/';
    private $authUser;
    private $authPass;
    private $client;
    private $signature;

    public function __construct($authUser, $authPass, $signature)
    {
        $this->authUser = $authUser;
        $this->authPass = $authPass;
        $this->signature = $signature;
        $this->client = new Client(['auth' => [$this->authUser, $this->authPass]]);
    }

    public function processContentList($contentList)
    {
        if (empty($contentList)) {
            return;
        }
        foreach ($contentList as $content) {
            $dataSearch = $this->searchContent($content);
            if($this->ContentIsDiferent($content, $dataSearch)){
                $this->saveContent($content, $dataSearch);
            }
        }
    }

    private function searchContent($content)
    {
        $pageId   = 0;
        $version  = 1;
        $bodyView = '';
        $uri     = self::BASE_URL . "?spaceKey={$content['Space']}&title={$content['Title']}&expand=version,body.view";
        $response = $this->client->get($uri);    
        
        $body = json_decode($response->getBody(), true);
        if (!$body['size']) {
            return compact('pageId', 'version', 'bodyView');
        }
        $pageId = $body['results'][0]['id'];
        $version = (int) $body['results'][0]['version']['number'] + 1;
        $bodyView = $body['results'][0]['body']['view']['value'];
        return compact('pageId', 'version', 'bodyView');
    }

    private function ContentIsDiferent($content, $dataSearch)
    {
        if (!$dataSearch['bodyView']) {
            return true;
        }
        $content['Content']     = nl2br($content['Content']);
        $content['Content']     = str_replace("<br />", "<br/>", $content['Content']);
        $dataSearch['bodyView'] = trim(preg_replace('/<br\/><br\/>@PhpDoc(.)*/', '', $dataSearch['bodyView'])); 
        if($dataSearch['bodyView'] != $content['Content']){
            return true;
        }
    }

    private function saveContent($content, $dataSearch)
    {
        $data = [
            'status'  => 'current',
            'version' => ['number' => $dataSearch['version']],
            'title'   => $content['Title'],
            'type'    => 'page',
            'body'    => [
                'editor' => [
                    'value'          => nl2br($content['Content'])."<br/><br/>".$this->signature,
                    'representation' => 'editor',
                    'content'        => ['id' => $dataSearch['pageId']]
                ]
            ],
            'space' => ['key' => $content['Space']]
        ];

        $url = self::BASE_URL;
        if ($dataSearch['pageId']) {
            $data['id'] = $dataSearch['pageId'];
            $data['body']['editor']['content'] = ['id' => $dataSearch['pageId']];
            $url .= $dataSearch['pageId'];
            echo $url;
            return $this->client->put($url, ['json' => $data]);
        }

        return $this->client->post($url, ['json' => $data]);
    }
}
<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiMLService{
    private $httpClient;

    /** @var  */
    private $user;
    private $password;
    private $accessToken;
    private $request;

    public function __construct(HttpClientInterface $client, RequestStack $requestStack)
    {
        $this->httpClient  = $client;
        $this->requestStack = $requestStack;

    }

    /**
     * Obtiene el token para consultas. El mismo vence a als 23:59 de cada día.
     */
    public function genToken(){
        $body = [
            "grant_type" => "refresh_token",
            "client_id" => '2466090627065519',
            "client_secret" => 'AILOvxMNeCwgNqDjrjjWWcThtp40JaMP' ,
            "refresh_token" => "TG-62d16a0bc9e74a001316df84-1160845094"
        ]; 
        $data = $this->callApi("https://api.mercadolibre.com/oauth/token", null, 'POST', $body, false);
        if ($data) {
            $session = $this->request->getSession(); 
            $session->set('ACCESS_TOKEN', $data["access_token"]);
            $this->accessToken = $data["access_token"];
        } 
        return ($data);
    }

    /**
     * @param int $codigo
     */
    public function fetchByCodigo($codigo, Request $request)
    { $this->request = $request;
        $data = $this->callApi("https://api.mercadolibre.com/products/search",[
           'status' => 'active',
           'site_id' => 'MLA',
           'product_identifier' => $codigo
        ]);
        if(!$data) {
            $session = $this->request->getSession();
            $session->set('ACCESS_TOKEN', null);
            $data = $this->callApi("https://api.mercadolibre.com/products/search",[

            ]);
        }
        return $data;
    }

    public function callApi($uri, $query = null, $method = 'GET', $body = null, $setToken = true)
    {
        if($setToken) {
            $session = $this->request->getSession();
            $this->accessToken = $session->get('ACCESS_TOKEN', null);
            if(!$this->accessToken){
                $this->genToken();
            }
        }
        $options = []; 
        if($setToken) {
            $options['headers']['Authorization'] = 'Bearer '.$this->accessToken;
        }
        if (is_array($query)) {
            $options['query'] = $query;
        }
        if (!empty($body)) {
            $options['body'] = $body;
        }
        $data = null;
        try {
             
            $response = $this->httpClient->request($method, $uri, $options);
            if($response->getStatusCode() == 200){
                $data = $response->toArray();  
            }
        } catch (\Exception $e) {
        } 
        
        return $data;
    }
}

<?php

class ValiAPIImport
{
    public $errorCode;
    public $apiRoot = "https://www.vali.bg/api/v1";
    public $apiToken = "";
    public $useXML = false;

    public function __construct($apiToken, $useXML = false)
    {
        $this->apiToken = $apiToken;
        $this->useXML = $useXML;
    }

    private function getRequest($uri)
    {
        $socket = curl_init();
        curl_setopt($socket, CURLOPT_URL, $this->apiRoot . $uri);
        curl_setopt($socket, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($socket, CURLOPT_HTTPHEADER, [
            'Accept: application/' . ($this->useXML ? 'xml' : 'json'),
            'Authorization: Bearer ' . $this->apiToken,
        ]);
        $result = curl_exec($socket);
        $this->errorCode = curl_getinfo($socket, CURLINFO_HTTP_CODE);
        curl_close($socket);

        return $result;
    }

    public function getCategories()
    {
        return $this->getRequest("/categories");
    }

    public function getManufacturers()
    {
        return $this->getRequest("/manufacturers");
    }

    public function getParameters($categoryId)
    {
        return $this->getRequest("/parameters/$categoryId");
    }

    public function getProduct($productId, $full = false)
    {
        return $this->getRequest("/product/$productId" . ($full ? "/full" : ""));
    }

    public function getProductsByCategory($categoryId, $full = false)
    {
        return $this->getRequest("/products/by_category/$categoryId" . ($full ? "/full" : ""));
    }

    public function getProductsByManufacturer($manufacturerId, $full = false)
    {
        return $this->getRequest("/products/by_manufacturer/$manufacturerId" . ($full ? "/full" : ""));
    }
}

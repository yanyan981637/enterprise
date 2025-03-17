<?php
namespace Mitac\Theme\Api\Data;

interface ZohoAccessTokenInterface
{
    const ID = 'id';
    const ACCESS_TOKEN = 'access_token';
    const STOER_ID = 'store_id';
    public function getId();
    public function setId($id);
    public function getAccessToken();
    public function getStoreId();
    public function setAccessToken($accessToken);
    public function setStoreId($storeId);
}

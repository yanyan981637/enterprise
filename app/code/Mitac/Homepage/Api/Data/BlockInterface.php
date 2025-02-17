<?php
namespace Mitac\Homepage\Api\Data;

interface BlockInterface
{
    const KEY_ID          = 'banners_id';
    const KEY_STORES_ID   = 'stores_id';
    const KEY_SORT_ID     = 'sort_id';
    const KEY_TYPE        = 'type';
    const KEY_TITLE       = 'title';
    const KEY_TEXT        = 'text';
    const KEY_BUTTON      = 'button';
    const KEY_IMG         = 'img';
    const KEY_PAGE_IDENTIFIER =  'PageIdentifier';
    const KEY_CMS_PAGE_ID = 'cms_page_id';
    const KEY_URL         = 'url';
    const KEY_YOUTUBE     = 'youtube';
    const KEY_CREATED_AT  = 'created_at';
    const KEY_UPDATED_AT  = 'updated_at';
    
    public function getStoresId();
    public function getSortId();
    public function getType();
    public function getTitle();
    public function getText();
    public function getButton();
    public function getPageIdentifier();
    public function getCmsPageId();
    public function getUrl();
    public function getYoutube();
    public function getCreatedAt();
  	public function getUpdatedAt();
    
    public function setStoresId($stores);
    public function setSortId($sortid);
    public function setType($type);
    public function setTitle($title);
    public function setText($text);
    public function setButton($button);
    public function setPageIdentifier($PageIdentifier);
    public function setCmsPageId($cmspageid);
    public function setUrl($url);
    public function setYoutube($youtube);
}

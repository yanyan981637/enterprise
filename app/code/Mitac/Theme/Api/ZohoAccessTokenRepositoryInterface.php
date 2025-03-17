<?php
namespace Mitac\Theme\Api;

use Mitac\Theme\Api\Data\ZohoAccessTokenInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Yandex\Allure\Adapter\Annotation\Parameter;

interface ZohoAccessTokenRepositoryInterface
{
    /**
     * @param int $id
     * @return \Mitac\Theme\Api\Data\ZohoAccessTokenInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param \Mitac\Theme\Api\Data\ZohoAccessTokenInterface $zohoAccessToken
     * @return \Mitac\Theme\Api\Data\ZohoAccessTokenInterface
     */
    public function save(ZohoAccessTokenInterface $zohoAccessToken);

    /**
     * @param \Mitac\Theme\Api\Data\ZohoAccessTokenInterface $zohoAccessToken
     * @return bool
     */
    public function delete(ZohoAccessTokenInterface $zohoAccessToken);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Mitac\Theme\Api\Data\ZohoAccessTokenSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    public function getExitAccessToken($storeId = null);

    public function setNewAccessToken($accessToken, $storeId = null);
}

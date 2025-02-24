<?php
namespace Mitac\Theme\Api;

use Mitac\Theme\Api\Data\ColorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Yandex\Allure\Adapter\Annotation\Parameter;

interface ColorRepositoryInterface
{
    /**
     * @param int $colorId
     * @return \Mitac\Theme\Api\Data\ColorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($colorId);

    /**
     * @param \Mitac\Theme\Api\Data\ColorInterface $color
     * @return \Mitac\Theme\Api\Data\ColorInterface
     */
    public function save(ColorInterface $color);

    /**
     * @param \Mitac\Theme\Api\Data\ColorInterface $color
     * @return bool
     */
    public function delete(ColorInterface $color);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Mitac\Theme\Api\Data\ColorSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Parameters: int $id,
     * Parameters: string $pageType, ColorInterface::CATEGORY_PAGE / PRODUCT_PAGE / CMS_PAGE / BLOG_CATEGORY_PAGE / BLOG_PAGE / CUSTOM_URL
     */
    public function getColorByPage($id, $pageType = ColorInterface::CATEGORY_PAGE, $storeId = null);
}

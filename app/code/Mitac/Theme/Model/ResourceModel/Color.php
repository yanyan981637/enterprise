<?php
namespace Mitac\Theme\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Color extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var DateTime
     */
    protected $date;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        DateTime $date,
        $connectionName = null
    ) {
        $this->date = $date;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('mitac_theme_color', 'color_id');
    }

    /**
     * 在保存前處理數據
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {

        // 如果是新對象，確保 color_id 為 null
        if ($object->isObjectNew()) {
            $object->setId(null);
        }

        // 設置更新時間
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        // 處理數組字段
        $arrayFields = [
            'store_ids',
            'category_page',
            'product_page',
            'cms_page',
            'blog_category_page',
            'blog_page'
        ];

        foreach ($arrayFields as $field) {
            $value = $object->getData($field);
            if (is_array($value)) {
                // 過濾空值並轉換為字符串
//                $value = array_filter($value);
                $object->setData($field, implode(',', $value));
            }
        }

        $result = parent::_beforeSave($object);

        return $result;
    }

    /**
     * 在加載後處理數據
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        // 處理數組字段
        $arrayFields = [
            'store_ids',
            'category_page',
            'product_page',
            'cms_page',
            'blog_category_page',
            'blog_page'
        ];

        foreach ($arrayFields as $field) {
            $value = $object->getData($field);
            if (!empty($value)) {
                $object->setData($field, explode(',', $value));
            }
        }

        return parent::_afterLoad($object);
    }
}

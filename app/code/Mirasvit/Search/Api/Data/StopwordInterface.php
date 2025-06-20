<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Api\Data;

interface StopwordInterface
{
    const TABLE_NAME = 'mst_search_stopword';

    const ID       = 'stopword_id';
    const TERM     = 'term';
    const STORE_ID = 'store_id';

    public function getId(): ?int;


    /**
     * @return string
     */
    public function getTerm();

    /**
     * @param string $input
     *
     * @return $this
     */
    public function setTerm($input);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $input
     *
     * @return $this
     */
    public function setStoreId($input);
}

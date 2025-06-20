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


namespace Mirasvit\SearchSphinx\SphinxQL\Drivers;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
abstract class ResultSetBase implements ResultSetInterface
{
    /**
     * @var int
     */
    protected $num_rows = 0;

    /**
     * @var null|int
     */
    protected $current_row = null;

    /**
     * @var int
     */
    protected $affected_rows = 0; // leave to 0 so SELECT etc. will be coherent

    /**
     * Checks that a row actually exists
     *
     * @param int $num The number of the row to check on
     * @return bool True if the row exists
     */
    public function hasRow($num)
    {
        return $num >= 0 && $num < $this->num_rows;
    }

    /**
     * Checks that a next row exists
     *
     * @return bool True if there's another row with a higher index
     */
    public function hasNextRow()
    {
        return $this->current_row === null
            ? (boolean)$this->num_rows
            : $this->current_row + 1 < $this->num_rows;
    }

    /**
     * Returns the number of rows affected by the query
     * This will be 0 for SELECT and any query not editing rows
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * Returns the number of rows in the result set
     *
     * @return int The number of rows in the result set
     */
    public function getCount()
    {
        return $this->num_rows;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->hasRow($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->toRow($offset)->fetchAssoc();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     *
     * @codeCoverageIgnore
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     *
     * @codeCoverageIgnore
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        return $this->fetchAssoc();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        if ($this->hasNextRow()) {
            $this->toNextRow();
        } else {
            $this->current_row++;
        }

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key(): mixed
    {
        return (int)$this->current_row;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return $this->hasRow($this->current_row);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        if ($this->getCount()) {
            $this->toRow(0);
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count(): int
    {
        return $this->getCount();
    }
}

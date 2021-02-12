<?php

namespace Suite\Api;

class ContactListChunkIterator implements \Iterator
{
    /**
     * @var ContactListChunkFetcher
     */
    private $chunkFetcher;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var int
     */
    private $contactListId;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var array
     */
    private $currentChunk = [];


    public function __construct(ContactListChunkFetcher $chunkFetcher, int $customerId, int $contactListId, int $chunkSize = 10000)
    {
        $this->chunkFetcher = $chunkFetcher;
        $this->chunkSize = $chunkSize;
        $this->customerId = $customerId;
        $this->contactListId = $contactListId;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->currentChunk;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->page++;
        $this->loadPage();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->page;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return !empty($this->currentChunk);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->page = 0;
        $this->loadPage();
    }

    private function loadPage()
    {
        $this->currentChunk = $this->chunkFetcher->getContactsOfList(
            $this->customerId,
            $this->contactListId,
            $this->chunkSize,
            $this->page * $this->chunkSize
        );
    }
}

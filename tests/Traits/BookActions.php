<?php

namespace Tests\Traits;

trait BookActions
{
    /**
     * @param array $params
     * @param bool  $editMode
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function getBooks($params = [], $editMode = false)
    {
        return $this->json('get', route('books.index'), $params, ['Edit-Mode' => $editMode]);
    }

    /**
     * @param array $book
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function postCreateBook($book = [])
    {
        return $this->json('post', route('books.store'), $book);
    }

    /**
     * @param int  $id
     * @param bool $editMode
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function destroyBook($id = 1, $editMode = false)
    {
        return $this->json('delete', route('books.destroy', ['book' => $id]), [], ['Edit-Mode' => $editMode]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function forceDestroyBook($id = 1)
    {
        return $this->json('delete', route('books.force_destroy', ['id' => $id]));
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function getBook($id = 1, $params = [])
    {
        return $this->json('get', route('books.show', ['book' => $id]), $params);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function updateBook($id = 1, $data = [])
    {
        return $this->json('put', route('books.update', ['book' => $id]), $data);
    }
}

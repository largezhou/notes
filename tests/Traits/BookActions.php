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
     * @param int  $id
     * @param bool $editMode
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function forceDestroyBook($id = 1, $editMode = false)
    {
        return $this->json('delete', route('books.force_destroy', ['deleted-book' => $id]), [], ['Edit-Mode' => $editMode]);
    }

    /**
     * @param int   $id
     * @param array $params
     * @param bool  $editMode
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function getBook($id = 1, $params = [], $editMode = false)
    {
        return $this->json('get', route('books.show', ['book' => $id]), $params, ['Edit-Mode' => $editMode]);
    }

    /**
     * @param int   $id
     * @param array $data
     * @param bool  $editMode
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function updateBook($id = 1, $data = [], $editMode = false)
    {
        return $this->json('put', route('books.update', ['book' => $id]), $data, ['Edit-Mode' => $editMode]);
    }
}

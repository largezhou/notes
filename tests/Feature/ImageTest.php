<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class ImageTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    public function testCreateImage()
    {
        $this->login();

        $image = UploadedFile::fake()->image('image.jpg');

        $filePath = '/uploads/' . md5_file($image) . '.jpg';

        $this->postCreateResource('images', ['image' => $image])
            ->assertStatus(201)
            ->assertJson(['src' => $filePath]);

        $this->assertFileExists(public_path($filePath));
    }
}

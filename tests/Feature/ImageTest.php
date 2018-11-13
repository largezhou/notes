<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImageTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreateImage()
    {
        $this->login();

        $image = UploadedFile::fake()->image('image.jpg');

        $filePath = '/uploads/' . md5_file($image) . '.jpg';

        $this->json('post', route('images.store'), ['image' => $image])
            ->assertStatus(201)
            ->assertJson(['src' => $filePath]);

        $this->assertFileExists(public_path($filePath));
    }
}

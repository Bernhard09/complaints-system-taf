<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        app()->setLocale('id');
        $translated = __('Workspace');
        
        $this->assertEquals('Ruang Kerja', $translated);

        app()->setLocale('en');
        $this->assertEquals('Workspace', __('Workspace'));
    }
}

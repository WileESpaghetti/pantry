<?php
declare(strict_types=1);

namespace HtmlBookmarks\Tests\Unit\Rules;

use HtmlBookmarks\Rules\SimpleFileName;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Validator;

class SimpleFileNameTest extends TestCase {

    /** @test */
    public function test_rule(): void {
        $tests = [
            // valid file names
            ['test' => 'default file name', 'filename' => 'bookmarks.html', 'passes' => true],
            ['test' => 'no file extension', 'filename' => 'bookmarks', 'passes' => true],
            ['test' => 'spaces', 'filename' => 'my bookmarks.html', 'passes' => true],
            ['test' => 'single letter', 'filename' => 'e', 'passes' => true],

            // invalid file names
            ['test' => 'empty string', 'filename' => '', 'passes' => false],
            ['test' => 'multiple spaces', 'filename' => 'my  bookmarks.html', 'passes' => false],
            ['test' => 'mixed separators', 'filename' => 'my  -bookmarks.html', 'passes' => false],
            ['test' => 'ends with separators', 'filename' => 'bookmarks-.html', 'passes' => false],
        ];

        foreach ($tests as $t) {
            $file = UploadedFile::fake()->create($t['filename']);
            $validator = Validator::make([
                'file' => $file
            ],[
                'file' => [new SimpleFileName()]
            ]);

            $this->assertEquals($t['passes'], $validator->passes(), "validation rule did not match testing for {$t['test']} filename: \"{$t['filename']}\"");
        }
    }

    /** @test */
    public function test_keeps_existing_validation_results(): void {
        $file = UploadedFile::fake()->create('bookmarks.html');
        $validator = Validator::make([
            'file' => $file
        ],[
            'file' => ['mimes:json', new SimpleFileName()]
        ]);

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function test_translates_failure_results(): void {
        $file = UploadedFile::fake()->create('book--marks.html');
        $validator = Validator::make([
            'file' => $file
        ],[
            'file' => [new SimpleFileName()]
        ]);

        $err = $validator->errors()->first('file');
        $this->markTestIncomplete();
    }

    public function test_validates_strings(): void {
        $this->markTestIncomplete();
    }
}

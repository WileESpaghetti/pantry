<?php

namespace HtmlBookmarks\Tests\Unit;

use Pantry\Services\BookmarkLintService;
use Tests\TestCase;

class BookmarkLintServiceTest extends TestCase
{
    /** @test */

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_isNotWebLink(): void
    {
        $tests = [
            'valid http url' => ['url' => 'http://www.example.com', 'is_not_web_link' => false],
            'valid https url' => ['url' => 'https://www.example.com', 'is_not_web_link' => false],
            'incomplete http protocol' => ['url' => 'http:www.example.com', 'is_not_web_link' => true],
            'URL with incorrect protocol case' => ['url' => 'HTTP://www.example.com', 'is_not_web_link' => true],
            'about URL' => ['url' => 'about:about', 'is_not_web_link' => true],
            'chrome URL' => ['url' => 'chrome://settings', 'is_not_web_link' => true],
            'relative path' => ['url' => '/home/example', 'is_not_web_link' => true],
            'FTP protocol' => ['url' => 'ftp://mirror.example.com', 'is_not_web_link' => true],
            'no protocol' => ['url' => '127.0.0.1', 'is_not_web_link' => true],
            'protocol relative url' => ['url' => '//www.example.com', 'is_not_web_link' => true],
//            '' => ['url' => '', 'is_not_web_link' => true],
        ];

        $linter = new BookmarkLintService();
        foreach($tests as $testName => $t) {
            $this->assertEquals( $t['is_not_web_link'], $linter->isNotWebLink($t['url']), $testName);
        }
    }
}

<?php
declare(strict_types=1);

namespace HtmlBookmarks\Tests\Unit;

use HtmlBookmarks\Services\HtmlBookmarkParserLoggerListener;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

/**
 * TODO
 * test log message context
 */
class HtmlBookmarkParserLoggerListenerTest extends TestCase
{
    /** @test */

    public function test_can_swap_loggers(): void
    {
        /** @var LoggerInterface $initialLogger */
        $initialLogger = $this->partialMock(LoggerInterface::class, function(MockInterface $mock) {
            $mock->shouldNotReceive('info');
        });

        /** @var LoggerInterface $altLogger */
        $altLogger = $this->partialMock(LoggerInterface::class, function(MockInterface $mock) {
            $mock->shouldReceive('info')->once();
        });

        $parserLogger = new HtmlBookmarkParserLoggerListener($initialLogger);
        $parserLogger->setLogger($altLogger);

        $parserLogger->info('hello world');
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_be_used_as_a_logger(): void
    {
        $logger = new HtmlBookmarkParserLoggerListener(new NullLogger());
        self::assertInstanceOf('Psr\Log\LoggerInterface', $logger);
    }

    public function test_proxies_logger_functions() {
        $loggerSpy = $this->createMock('Psr\Log\LoggerInterface');

        $logger = new HtmlBookmarkParserLoggerListener($loggerSpy);

        $loggerMethods = get_class_methods('Psr\Log\LoggerInterface');
        foreach($loggerMethods as $method) {
            $loggerSpy->expects($this->once())->method($method);

            if ($method === 'log') {
                call_user_func([$logger, $method], 'level', 'message', []);
            } else {
                call_user_func([$logger, $method], 'message', []);
            }
        }
    }

    // FIXME can just test the event handlers are called with the appropriate args and then we don't have to test the protected function
    public function test_parsing_log_messages() {
        $logger = new HtmlBookmarkParserLoggerListener(new NullLogger());

        $loggerSpy = new ReflectionClass($logger);
        $method = $loggerSpy->getMethod('parseLogMessage');
        $method->setAccessible(true);

        $tests = [
            'starting_to_parse_file' => [
                'in' => 'Starting to parse bookmarks.html',
                'out' => [
                    'message' => '',
                    'context' => [
                        'filename' => 'bookmarks.html'
                    ]
                ]
            ],

            /*
             * treat messages with missing data as an `unknown_message`
             *
             * IMPLEMENTATION NOTES: had questions about whether I needed to check if there were matches from
             * `preg_match` to avoid an array index error when getting the matching filename. This revealed that by
             * switching the filename submatch from `(.*)` to `(.+)` I was guaranteed to have a matched filename.
             */
            'starting_to_parse_no_filename_given' => [
                'in' => 'Starting to parse ',
                'out' => [
                    'message' => 'Starting to parse ',
                    'context' => []
                ]
            ],

            'starting_to_parse_line' => [
                'in' => 'PARSING LINE #13',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13
                    ]
                ]
            ],

            'get_line_content' => [
                'in' => '[#13] Content: <DT><DL>THIS IS NOT VALID MARKUP</DL></DT>',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'line' => '<DT><DL>THIS IS NOT VALID MARKUP</DL></DT>'
                    ]
                ]
            ],

            'header_found' => [
                'in' => '[#13] Header found: personal toolbar',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'header' => 'personal toolbar'
                    ]
                ]
            ],

            'header_ended' => [
                'in' => '[#13] Header ended: personal toolbar',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'header' => 'personal toolbar'
                    ]
                ]
            ],

            'link_found' => [
                'in' => '[#13] Link found',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                    ]
                ]
            ],

            'link_url_found' => [
                'in' => '[#13] URL found: https://example.test',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'url' => 'https://example.test'
                    ]
                ]
            ],

            'link_url_empty' => [
                'in' => '[#13] Empty URL',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                    ]
                ]
            ],

            'link_icon_found' => [
                'in' => '[#13] ICON found: https://example.test',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'icon_url' => 'https://example.test',
                    ]
                ]
            ],

            'link_icon_empty' => [
                'in' => '[#13] Empty ICON',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                    ]
                ]
            ],

            'link_title_found' => [
                'in' => '[#13] Title found: my example title',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'title' => 'my example title'
                    ]
                ]
            ],

            'link_title_empty' => [
                'in' => '[#13] Empty title',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                    ]
                ]
            ],

            'link_content_found' => [
                'in' => '[#13] Content found: description of the link...',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'content' => 'description of the link...'
                    ]
                ]
            ],

            'link_content_empty' => [
                'in' => '[#13] Empty content',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                    ]
                ]
            ],

            'link_tag_list' => [
                'in' => '[#13] Tag list: first second third',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'tags' => [
                            'first',
                            'second',
                            'third'
                        ]
                    ]
                ]
            ],

            'link_date_found' => [
                'in' => '[#13] Date: 1654051629',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'timestamp' => 1654051629,
                        'date_utc' => '2022-06-01T02:47:09+00:00'
                    ]
                ]
            ],

            'link_visibility' => [
                'in' => '[#13] Visibility: public',
                'out' => [
                    'message' => '',
                    'context' => [
                        'line_number' => 13,
                        'visibility' => 'public',
                        'is_private' => false
                    ]
                ]
            ],

            'parsing_complete' => [
                'in' => 'File parsing ended',
                'out' => [
                    'message' => 'finished parsing bookmark file',
                    'context' => []
                ]
            ],

            'unknown_message' => [
                'in' => 'generic log message',
                'out' => [
                    'message' => 'generic log message',
                    'context' => []
                ]
            ]
        ];

        foreach($tests as $testName => $test) {
            try {
                $parsed = $method->invokeArgs($logger, [$test['in']]);
            } catch (ReflectionException $e) {
                $this->fail("could not run method: \"{$method->getName()}\"");
            }

            $this->assertEquals($test['out'], $parsed, $testName);
        }
    }
}

<?php
declare(strict_types=1);

namespace HtmlBookmarks\Services;

use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Acts as a proxy to the Laravel default logger and the one used by NetscapeBookmarkParser
 *
 * We are using this approach instead of using the service container to configure NetscapeBookmarkParser so that we
 * can modify the messages in transit and send events based on those messages. I am unaware of whether or not Monolog
 * provides a way to do that. A quick skim of the Laravel and Monolog docs makes it seem like I have to do a lot of
 * extra logger configuration to avoid the need to parse all incoming log messages. Right now that feels a bit more
 * complex than needed. Making the change should be easy enough if needed later.
 *
 * @see \Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser
 * @link  https://laravel.com/docs/9.x/logging#building-log-stacks
 * @link https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#using-processors
 *
 * TODO
 * localize log messages
 *
 * TODO
 * create proper event listener / event creator interfaces
 */
class HtmlBookmarkParserLoggerListener implements LoggerInterface, LoggerAwareInterface
{
    // discovered via: grep logger NetscapeBookmarkParser
    const PATTERN_PARSE_START = '/Starting to parse (.+)/';
    const PATTERN_LINE_START = '/PARSING LINE #(\d+)/';
    const PATTERN_LINE_CONTENT = '/\[#(\d+)\] Content: (.+)/';
    const PATTERN_HEADER_FOUND = '/\[#(\d+)\] Header found: (.+)/';
    const PATTERN_HEADER_END = '/\[#(\d+)\] Header ended: (.+)/';
    const PATTERN_LINK_FOUND = '/\[#(\d+)\] Link found/';
    const PATTERN_URL_FOUND = '/\[#(\d+)\] URL found: (.+)/';
    const PATTERN_URL_EMPTY = '/\[#(\d+)\] Empty URL/';
    const PATTERN_ICON_FOUND = '/\[#(\d+)\] ICON found: (.+)/';
    const PATTERN_ICON_EMPTY = '/\[#(\d+)\] Empty ICON/';
    const PATTERN_TITLE_FOUND = '/\[#(\d+)\] Title found: (.+)/';
    const PATTERN_TITLE_EMPTY = '/\[#(\d+)\] Empty title/';
    const PATTERN_CONTENT_FOUND = '/\[#(\d+)\] Content found: (.+)/';
    const PATTERN_CONTENT_EMPTY = '/\[#(\d+)\] Empty content/';
    const PATTERN_TAG_LIST = '/\[#(\d+)\] Tag list: (.+)/';
    const PATTERN_DATE_FOUND = '/\[#(\d+)\] Date: (\d+)/';
    const PATTERN_VISIBILITY = '/\[#(\d+)\] Visibility: (.+)/';
    const PATTERN_PARSE_END = '/File parsing ended/';

    private LoggerInterface $_logger;

    #[ArrayShape(['message' => "string", 'context' => "array"])] protected function parseLogMessage($message): array
    {
        $finalMessage = '';
        $context = [];

        $matches = [];
        switch ($message) {
            case preg_match(self::PATTERN_PARSE_START, $message, $matches) > 0:
                $context['filename'] = $matches[1];
                break;
            case preg_match(self::PATTERN_LINE_START, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_LINE_CONTENT, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['line'] = $matches[2];
                break;
            case preg_match(self::PATTERN_HEADER_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['header'] = $matches[2];
                break;
            case preg_match(self::PATTERN_HEADER_END, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['header'] = $matches[2];
                break;
            case preg_match(self::PATTERN_LINK_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_URL_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['url'] = $matches[2];
                break;
            case preg_match(self::PATTERN_URL_EMPTY, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_ICON_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['icon_url'] = $matches[2];
                break;
            case preg_match(self::PATTERN_ICON_EMPTY, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_TITLE_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['title'] = $matches[2];
                break;
            case preg_match(self::PATTERN_TITLE_EMPTY, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_CONTENT_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['content'] = $matches[2];
                break;
            case preg_match(self::PATTERN_CONTENT_EMPTY, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                break;
            case preg_match(self::PATTERN_TAG_LIST, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['tags'] = explode(' ', $matches[2]);
                break;
            case preg_match(self::PATTERN_DATE_FOUND, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['timestamp'] = $matches[2];
                $context['date_utc'] = gmdate(DATE_ATOM, (int) $matches[2]);
                break;
            case preg_match(self::PATTERN_VISIBILITY, $message, $matches) > 0:
                $context['line_number'] = $matches[1];
                $context['visibility'] = $matches[2];
                $context['is_private'] = $matches[2] !== 'public';
                break;
            case preg_match(self::PATTERN_PARSE_END, $message, $matches) > 0:
                $finalMessage = 'finished parsing bookmark file';
                break;
            default:
                /*
                 * FIXME
                 * do we want to log some sort of warning so the message shows up as needing to be handled
                 *
                 * TODO
                 * should we try to parse with some heuristics to try and get known values from the message
                 * ex. line_number => /\[#(\d+\])/
                 */
                $finalMessage = $message;
                break;
        }

        return [
            'message' => $finalMessage,
            'context' => $context
        ];
    }

    public function __construct(LoggerInterface $logger) {
        $this->_logger = $logger;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->_logger = $logger;
    }

    public function emergency($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->emergency($parsed['message'], $parsed['context']);
    }

    public function alert($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->alert($parsed['message'], $parsed['context']);
    }

    public function critical($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->critical($parsed['message'], $parsed['context']);
    }

    public function error($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->error($parsed['message'], $parsed['context']);
    }

    public function warning($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->warning($parsed['message'], $parsed['context']);
    }

    public function notice($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->notice($parsed['message'], $parsed['context']);
    }

    public function info($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->info($parsed['message'], $parsed['context']);
    }

    public function debug($message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->debug($parsed['message'], $parsed['context']);
    }

    public function log($level, $message, array $context = []) {
        $parsed = $this->parseLogMessage($message);

        $this->_logger->log($level, $parsed['message'], $parsed['context']);
    }

    // HANDLE EVENTS
//    public function onParseStart(callable $callback) {
//        $this->_logger->info('Starting to parse ' . $filename);
//    }
//
//    public function OnLineStart(callable $callback) {
//        $this->_logger->info('PARSING LINE #' . $lineNumber);
//    }
//
//    public function OnLineContent(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Content: ' . $line);
//    }
//
//    public function onHeaderFound(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Header found: ' . implode(' ', $tag));
//    }
//
//    public function onHeaderEnd(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Header ended: ' . implode(' ', $tag ?? []));
//    }
//
//    public function onLinkFound(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Link found');
//    }
//
//    public function onUrlFound(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] URL found: ' . $href[1]);
//    }
//
//    public function onUrlEmpty(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Empty URL');
//    }
//
//    public function onIconFound(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] ICON found: ' . $href[1]);
//    }
//
//    public function onIconEmpty(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Empty ICON');
//    }
//
//    public function onTitleFound(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Title found: ' . $title[1]);
//    }
//
//    public function onTitleEmpty(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Empty title');
//    }
//
//    public function onContentFound(callable $callback) {
//        $this->_logger->debug( '[#' . $lineNumber . '] Content found: ' . substr($description[2], 0, 50) . '...' );
//        $this->_logger->debug('[#' . $lineNumber . '] Content found: ' . substr($note[1], 0, 50) . '...');
//    }
//
//    public function onContentEmpty(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Empty content');
//    }
//
//    /**
//     * This might not be a 100% accurate list of the tags that were actually assigned.
//     *
//     * If a comma was detected to use as the separator, then it is possible for a tag to have spaces.
//     * The logger just joins all tags with spaces, so it looks like they are all one word tags. There is
//     * also a chance that it includes duplicates because of this.
//     */
//    public function onTagList(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Tag list: ' . implode(' ', $item['tags']));
//    }
//
//    public function onDate(callable $callback) {
//        $this->_logger->debug('[#' . $lineNumber . '] Date: ' . $item['time']);
//    }
//
//    public function onItemVisibility(callable $callback) {
//        $this->_logger->debug( '[#' . $lineNumber . '] Visibility: ' . ($item['pub'] ? 'public' : 'private') );
//    }
//
//    public function onParseEnd(callable $callback) {
//        $this->_logger->info('File parsing ended');
//    }
//
//    public function onUnknownMessage(callable $callback) {
//        // catch future changes
//    }
//
//    public function handleEvent($logLevel, $message, $context) {
//    }
}

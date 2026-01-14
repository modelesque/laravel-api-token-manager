<?php /** @noinspection PhpUnused */

namespace Modelesque\ApiTokenManager\Helpers;

use JetBrains\PhpStorm\Pure;

/**
 * Standardized error messages for making RESTful API requests.
 */
class ErrorMessage
{
    /**
     * @param string $resource
     * @param string $title
     * @param string $platform
     * @param string $loop
     * @return string
     * @see message
     */
    #[Pure]
    public static function forGetRequest(string $resource, string $title = '', string $platform = '', string $loop = ''): string
    {
        return self::message('GET', $resource, $title, $platform, $loop);
    }

    /**
     * @param string $resource
     * @param string $title
     * @param string $platform
     * @param string $loop
     * @return string
     * @see message
     */
    #[Pure]
    public static function forPostRequest(string $resource, string $title = '', string $platform = '', string $loop = ''): string
    {
        return self::message('POST', $resource, $title, $platform, $loop);
    }

    /**
     * @param string $resource
     * @param string $title
     * @param string $platform
     * @param string $loop
     * @return string
     * @see message
     */
    #[Pure]
    public static function forPatchRequest(string $resource, string $title = '', string $platform = '', string $loop = ''): string
    {
        return self::message('PATCH', $resource, $title, $platform, $loop);
    }

    /**
     * @param string $resource
     * @param string $title
     * @param string $platform
     * @param string $loop
     * @return string
     * @see message
     */
    #[Pure]
    public static function forPutRequest(string $resource, string $title = '', string $platform = '', string $loop = ''): string
    {
        return self::message('PUT', $resource, $title, $platform, $loop);
    }

    /**
     * @param string $resource
     * @param string $title
     * @param string $platform
     * @param string $loop
     * @return string
     * @see message
     */
    #[Pure]
    public static function forDeleteRequest(string $resource, string $title = '', string $platform = '', string $loop = ''): string
    {
        return self::message('DELETE', $resource, $title, $platform, $loop);
    }

    /**
     * @param string $method The REST method.
     * @param string $resource The thing being requested.
     * @param string $title The title of the resource.
     * @param string $provider The API provider (e.g. "spotify")
     * @param string $loop The iterative item in a loop that through the exception for which this message is needed.
     * @return string
     */
    #[Pure]
    public static function message(string $method, string $resource, string $provider = '', string $title = '', string $loop = ''): string
    {
        return sprintf(
            '%sError making %s request for %s%s%s.',
            ($loop ? "$loop " : ''),
            $method,
            $resource,
            ($title ? " \"$title\"" : ''),
            ($provider ? " from $provider" : '')
        );
    }

    /**
     * When logging errors or info within a loop, use this to add a tag in the log message.
     *
     * Basic output example: "[4/23]"
     * Detailed output example: "[Track 4/23 'Singing The For-Loop Blues']"
     *
     * @param int $i
     * @param int $total
     * @param string $type
     * @param string $title
     * @return string
     */
    #[Pure]
    public static function loopTag(int $i = 0, int $total = 0, string $type = '', string $title = ''): string
    {
        return sprintf(
            "[%s%s/%s%s] ",
            ($type ? $type . ' ' : ''),
            $i,
            $total,
            ($title ? " '$title'" : '')
        );
    }
}
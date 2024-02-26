<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Helpers;

final class Replace
{
    public static function start(string $subject, string $search, string $replace = ''): string
    {
        return self::strictReplace($subject, $search, $replace, '^%s');
    }

    public static function end(string $subject, string $search, string $replace = ''): string
    {
        return self::strictReplace($subject, $search, $replace, '%s$');
    }

    private static function strictReplace(string $subject, string $search, string $replace, string $pattern): string
    {
        $result = preg_replace(
            sprintf("#$pattern#", preg_quote($search, '#')),
            $replace,
            $subject,
            1,
        );
        if ($result === null) {
            return $search;
        }

        return $result;
    }
}

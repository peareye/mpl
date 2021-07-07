<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Utilities;

/**
 *  Piton Toolbox Class
 *
 *  Misc Helper Methods
 */
class Toolbox
{
    /**
     * Truncate HTML Text
     *
     * Accepts an HTML string, and returns just the unformatted text, truncated to the number of words
     * @param string $text Input HTML string
     * @param int, $characters Number of characters to return
     * @return string
     */
    public function truncateHtmlText(string $text, int $characters = 300): string
    {
        // Clean up html tags and special characters
        $text = preg_replace('/<[^>]*>/', ' ', $text);
        $text = str_replace("\r", '', $text); // replace with empty space
        $text = str_replace("\n", ' ', $text); // replace with space
        $text = str_replace("\t", ' ', $text); // replace with space
        $text = preg_replace('/\s+/', ' ', $text); // remove multiple consecutive spaces
        $text = preg_replace('/^[\s]/', '', $text); // Remove leading space from excerpt
        $text = preg_replace('/[\s]$/', '', $text); // Remove trailing space from excerpt

        // If we are already within the limit, just return the text
        if (mb_strlen($text) <= $characters) {
            return $text;
        }

        // Truncate to character limit if longer than requested
        $text = substr($text, 0, $characters);

        // We don't want the string cut mid-word, so search for the last space and trim there
        $lastSpacePosition = strrpos($text, ' ');
        if (isset($lastSpacePosition)) {
            // Cut the text at this last word
            $text = substr($text, 0, $lastSpacePosition);
        }

        return $text;
    }

    /**
     * Clean URLs
     *
     * Replaces any non alphanumeric or space characters with dashes
     * @param string $string Unformatted URL
     * @return string
     */
    public function cleanUrl(string $string): string
    {
        // First replace ampersands with the word 'and'
        $string = str_replace('&', 'and', $string);

        // Remove slashes
        $string = str_replace('/', '-', $string);

        // Strip out any single quotes
        $string = str_replace("'", '', $string);

        // Remove unwelcome characters, and replace with dashes
        $string = preg_replace('/[^a-zA-Z0-9]+/', '-', $string);

        // Lower case
        $string = mb_strtolower($string);

        // Finally remove any trailing dashes
        $string = trim($string, '-');

        return $string;
    }
}

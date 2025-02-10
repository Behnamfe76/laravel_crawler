<?php

namespace App\Helpers;

class SlugHelper
{
    /**
     * Create a random slug with 12 characters starting with a number and followed by uppercase letters.
     *
     * @return string The generated slug.
     */
    public function createRandomSlug(): string
    {
        // Generate a random number for the first character
        $firstChar = chr(rand(48, 57)); // ASCII 48-57 corresponds to 0-9

        // Generate the remaining 11 characters as uppercase letters
        $remainingChars = '';
        for ($i = 0; $i < 11; $i++) {
            $remainingChars .= chr(rand(65, 90)); // ASCII 65-90 corresponds to A-Z
        }

        return $firstChar . $remainingChars;
    }

    /**
     * Ensure the slug is unique by appending a number if necessary.
     *
     * @param string $slug The initial slug.
     * @param callable $isUniqueCallback A callback function to check uniqueness.
     * @param string $separator The separator used in the slug.
     * @return string The unique slug.
     */
    public function makeUnique(string $slug, callable $isUniqueCallback, string $separator = '-'): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (!$isUniqueCallback($slug)) {
            $slug = $originalSlug . $separator . $counter++;
        }

        return $slug;
    }
}

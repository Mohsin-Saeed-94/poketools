<?php

namespace App\DataMigration;

/**
 * Generic reusable helper functions for use in Data Migrations
 */
class Helpers
{
    /**
     * Remove nulls from the passed data
     *
     * @param array $data
     *
     * @return array
     */
    static function removeNulls(array $data): array
    {
        return array_filter(
            $data,
            function ($value) {
                return !is_null($value);
            }
        );
    }

    /**
     * Build a human-readable string for creating a range of numbers.
     *
     * @param int $min
     * @param int $max
     *
     * @return int|string
     */
    static function buildRangeString(int $min, int $max)
    {
        if ($min === $max) {
            return $min;
        } else {
            return "$min-$max";
        }
    }

    /**
     * Convert the fields listed in $fields to int.
     *
     * @param array $data
     * @param array $fields
     *
     * @return array
     */
    static function convertToInts(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int)$data[$field];
            }
        }

        return $data;
    }

}

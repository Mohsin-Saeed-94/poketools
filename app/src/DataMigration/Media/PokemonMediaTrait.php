<?php
/**
 * @file PokemonMediaTrait.php
 */

namespace App\DataMigration\Media;

/**
 * Helpers for handling the crazy way Pokemon media is organized upstream
 *
 * @package App\DataMigration\Media
 */
trait PokemonMediaTrait
{
    /**
     * Build the filename from species id and form identifier
     *
     * @param int $speciesId
     * @param string|null $form_identifier
     * @param string $ext
     *
     * @return string
     */
    protected function buildFilename(int $speciesId, ?string $form_identifier, string $ext): string
    {
        if ($form_identifier !== null) {
            $filename = sprintf('%s-%s', $speciesId, $form_identifier);
        } else {
            $filename = (string)$speciesId;
        }
        $filename .= '.'.$ext;

        return $filename;
    }
}
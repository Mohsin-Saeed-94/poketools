<?php


namespace App\DataMigration\Helpers;

use App\DataMigration\PokemonSpecies;
use App\Entity\Pokemon;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\VersionGroup;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use DragoonBoots\A2B\Exception\MigrationException;
use Ds\Map;

/**
 * Helpers when looking up pokemon data
 */
final class PokemonLookup
{
    private MigrationReferenceStoreInterface $referenceStore;
    private Map $species;
    private Map $pokemon;

    public function __construct(MigrationReferenceStoreInterface $referenceStore)
    {
        $this->referenceStore = $referenceStore;
        $this->species = new Map();
        $this->pokemon = new Map();
    }

    /**
     * @param VersionGroup $versionGroup
     * @param string $species
     *
     * @return PokemonSpeciesInVersionGroup
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     */
    public function lookupSpecies(VersionGroup $versionGroup, string $species): PokemonSpeciesInVersionGroup
    {
        if (!$this->species->hasKey($versionGroup->getId())) {
            $this->species->put($versionGroup->getId(), new Map());
        }
        /** @var Map $versionGroupSpecies */
        $versionGroupSpecies = $this->species->get($versionGroup->getId());
        if (!$versionGroupSpecies->hasKey($species)) {
            /** @var \App\Entity\PokemonSpecies $entity */
            $entity = $this->referenceStore->get(PokemonSpecies::class, ['identifier' => $species]);
            $versionGroupSpecies->put($species, $entity->findChildByGrouping($versionGroup));
        }

        return $versionGroupSpecies->get($species);
    }

    /**
     * @param VersionGroup $versionGroup
     * @param string $species
     * @param string $pokemon
     *
     * @return Pokemon
     * @throws MigrationException
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     */
    public function lookupPokemon(VersionGroup $versionGroup, string $species, string $pokemon): Pokemon
    {
        if (!$this->pokemon->hasKey($versionGroup->getId())) {
            $this->pokemon->put($versionGroup->getId(), new Map());
        }
        /** @var Map $versionGroupSpecies */
        $versionGroupSpecies = $this->pokemon->get($versionGroup->getId());
        if (!$versionGroupSpecies->hasKey($species)) {
            $versionGroupSpecies->put($species, new Map());
        }
        /** @var Map $speciesPokemon */
        $speciesPokemon = $versionGroupSpecies->get($species);
        if (!$speciesPokemon->hasKey($pokemon)) {
            $speciesInVersionGroup = $this->lookupSpecies($versionGroup, $species);
            $entity = null;
            foreach ($speciesInVersionGroup->getPokemon() as $checkPokemon) {
                if ($checkPokemon->getSlug() === $pokemon) {
                    $entity = $checkPokemon;
                    break;
                }
            }
            if (!$entity) {
                throw new MigrationException(
                    sprintf(
                        'Species "%s", Pokemon "%s", VersionGroup "%s" does not exist.',
                        $species,
                        $pokemon,
                        $versionGroup->getSlug()
                    )
                );
            }
            $speciesPokemon->put($pokemon, $entity);
        }

        return $speciesPokemon->get($pokemon);
    }
}

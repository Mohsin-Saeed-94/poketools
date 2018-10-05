<?php

namespace App\DataMigration;

use App\Entity\NatureBattleStylePreference;
use App\Entity\NaturePokeathlonStatChange;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Nature migration.
 *
 * @DataMigration(
 *     name="Nature",
 *     source="yaml:///%kernel.project_dir%/resources/data/nature",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Nature",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *       "App\DataMigration\Stat",
 *       "App\DataMigration\BerryFlavor",
 *       "App\DataMigration\BattleStyle",
 *       "App\DataMigration\PokeathlonStat"
 *     }
 * )
 */
class Nature extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @var \App\Entity\Nature $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $sourceData['stat_increased'] = $this->referenceStore->get(Stat::class, ['identifier' => $sourceData['stat_increased']]);
        $sourceData['stat_decreased'] = $this->referenceStore->get(Stat::class, ['identifier' => $sourceData['stat_decreased']]);
        $sourceData['flavor_likes'] = $this->referenceStore->get(BerryFlavor::class, ['identifier' => $sourceData['flavor_likes']]);
        $sourceData['flavor_hates'] = $this->referenceStore->get(BerryFlavor::class, ['identifier' => $sourceData['flavor_hates']]);

        $properties = [
            'name',
            'stat_increased',
            'stat_decreased',
            'flavor_likes',
            'flavor_hates',
        ];
        $this->mergeProperties($sourceData, $destinationData, $properties);

        foreach ($sourceData['battle_style_preferences'] as $battleStyle => $battleStylePreferenceData) {
            /** @var \App\Entity\BattleStyle $battleStyle */
            $battleStyle = $this->referenceStore->get(BattleStyle::class, ['identifier' => $battleStyle]);
            $battleStylePreference = $destinationData->getBattleStylePreferences()
                ->filter(
                    function (NatureBattleStylePreference $battleStylePreference) use ($battleStyle) {
                        return ($battleStyle === $battleStylePreference->getBattleStyle());
                    }
                )->first();
            if (!$battleStylePreference) {
                $battleStylePreference = new NatureBattleStylePreference();
                $battleStylePreference->setBattleStyle($battleStyle);
            }
            $this->mergeProperties($battleStylePreferenceData, $battleStylePreference);
            $destinationData->addBattleStylePreference($battleStylePreference);
        }

        foreach ($sourceData['pokeathlon_stat_changes'] as $pokeathlonStat => $pokeathlonStatChangeData) {
            /** @var \App\Entity\PokeathlonStat $pokeathlonStat */
            $pokeathlonStat = $this->referenceStore->get(PokeathlonStat::class, ['identifier' => $pokeathlonStat]);
            $pokeathlonStatChange = $destinationData->getPokeathlonStatChanges()
                ->filter(
                    function (NaturePokeathlonStatChange $pokeathlonStatChange) use ($pokeathlonStat) {
                        return ($pokeathlonStat === $pokeathlonStatChange->getPokeathlonStat());
                    }
                )->first();
            if (!$pokeathlonStatChange) {
                $pokeathlonStatChange = new NaturePokeathlonStatChange();
                $pokeathlonStatChange->setPokeathlonStat($pokeathlonStat);
            }
            $this->mergeProperties($pokeathlonStatChangeData, $pokeathlonStatChange);
            $destinationData->addPokeathlonStatChange($pokeathlonStatChange);
        }

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Nature();
    }
}

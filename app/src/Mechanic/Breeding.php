<?php
/**
 * @file Breeding.php
 */

namespace App\Mechanic;

use App\Entity\Embeddable\Range;
use App\Entity\Version;

/**
 * Breeding mechanics
 */
final class Breeding
{
    /**
     * Get the number of steps required to hatch an egg.
     *
     * This is complicated by the algorithm changing slightly between versions.
     *
     * Slightly fewer steps may be required depending on the version and if
     * the egg is picked up in the middle of an egg cycle.
     *
     * @param Version $version
     * @param int $eggCycles
     *
     * @return Range[]
     */
    public function hatchSteps(Version $version, int $eggCycles): array
    {
        $steps = [];

        switch ($version->getVersionGroup()->getGeneration()->getNumber()) {
            case 2:
                $stepsPerCycle = 256;
                $cycles = $eggCycles;

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles) - ($stepsPerCycle - 1));

                break;
            case 3:
                $stepsPerCycle = 256;
                $cycles = $eggCycles + 1;

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles));
                if ($version->getSlug() === 'emerald') {
                    $steps['With []{ability:flame-body} or []{ability:magma-armor}'] = (new Range())
                        ->setMax($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)))
                        ->setMin($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)));
                }

                break;
            case 4:
                $stepsPerCycle = 255;
                $cycles = $eggCycles + 1;

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles));
                $steps['With []{ability:flame-body} or []{ability:magma-armor}'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)));

                break;
            case 5:
                $stepsPerCycle = 257;
                $cycles = $eggCycles;

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles) - ($stepsPerCycle - 1));
                $steps['With []{ability:flame-body} or []{ability:magma-armor}'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)) - ($stepsPerCycle - 1));
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.875), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.875), $cycles)
                        - (floor($stepsPerCycle * 0.875) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑ and []{ability:flame-body} or []{ability:magma-armor}'] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.875), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.875), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.875) - 1)
                        );
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑↑'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.75), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.75), $cycles)
                        - (floor($stepsPerCycle * 0.75) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑↑ and []{ability:flame-body} or []{ability:magma-armor}'] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.75), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.75), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.75) - 1)
                        );
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑↑↑'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.5), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.5), $cycles)
                        - (floor($stepsPerCycle * 0.5) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:pass-powers} ↑↑↑ and []{ability:flame-body} or []{ability:magma-armor}'] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.5), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.5), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.5) - 1)
                        );

                break;
            case 6:
                $stepsPerCycle = 257;
                $cycles = $eggCycles;

                if ($version->getVersionGroup()->getSlug() === 'omega-ruby-alpha-sapphire') {
                    // Omega Ruby and Alpha Sapphire have a feature that acts the same as the hot body abilities.
                    $halfCyclesLabel = '[]{ability:flame-body}/[]{ability:magma-armor} or [Secret Pal]{mechanic:secret-pal} with "Take care of an Egg"';
                } else {
                    $halfCyclesLabel = '[]{ability:flame-body} or []{ability:magma-armor}';
                }

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles) - ($stepsPerCycle - 1));
                $steps['With '.$halfCyclesLabel] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)) - ($stepsPerCycle - 1));
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 1'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.875), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.875), $cycles)
                        - (floor($stepsPerCycle * 0.875) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 1 and '.$halfCyclesLabel] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.875), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.875), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.875) - 1)
                        );
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 2'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.75), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.75), $cycles)
                        - (floor($stepsPerCycle * 0.75) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 2 and '.$halfCyclesLabel] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.75), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.75), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.75) - 1)
                        );
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 3'] = (new Range())
                    ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.5), $cycles))
                    ->setMin(
                        $this->calcHatchSteps(floor($stepsPerCycle * 0.5), $cycles)
                        - (floor($stepsPerCycle * 0.5) - 1)
                    );
                $steps['With [Hatching Power]{mechanic:o-powers} Lv 3 '.$halfCyclesLabel] =
                    (new Range())
                        ->setMax($this->calcHatchSteps(floor($stepsPerCycle * 0.5), floor($cycles / 2)))
                        ->setMin(
                            $this->calcHatchSteps(floor($stepsPerCycle * 0.5), floor($cycles / 2))
                            - (floor($stepsPerCycle * 0.5) - 1)
                        );

                break;
            default:
                // Generation 7
                $stepsPerCycle = 257;
                $cycles = $eggCycles;

                $steps['Normally'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, $cycles))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, $cycles) - ($stepsPerCycle - 1));
                $steps['With []{ability:flame-body} or []{ability:magma-armor}'] = (new Range())
                    ->setMax($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)))
                    ->setMin($this->calcHatchSteps($stepsPerCycle, floor($cycles / 2)) - ($stepsPerCycle - 1));

                break;
        }

        return $steps;
    }

    /**
     * @param int $stepsPerCycle
     * @param int $eggCycles
     *
     * @return int
     */
    protected function calcHatchSteps(int $stepsPerCycle, int $eggCycles): int
    {
        return $stepsPerCycle * $eggCycles;
    }
}
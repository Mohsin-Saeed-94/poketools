<?php
/**
 * @file GaugeExtensionRuntime.php
 */

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

/**
 * Runtime for GaugeExtension
 */
class GaugeExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * Generate a linear gauge
     *
     * @param \Twig_Environment $twig
     * @param int ...$values
     *
     * @return string
     */
    public function gaugeLinear(\Twig_Environment $twig, ...$values)
    {
        $total = array_sum($values);

        return $twig->render(
            '_functions/gauge/linear.svg.twig',
            [
                'values' => $values,
                'total' => $total,
            ]
        );
    }
}
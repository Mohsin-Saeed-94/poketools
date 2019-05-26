<?php
/**
 * @file GaugeExtensionRuntime.php
 */

namespace App\Twig;

use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Runtime for GaugeExtension
 */
class GaugeExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * Generate a linear gauge
     *
     * @param Environment $twig
     * @param int ...$values
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function gaugeLinear(Environment $twig, ...$values)
    {
        // Assume a percentage if only one value passed.
        $total = count($values) > 1 ? array_sum($values) : 100;

        // Calculate percentages
        $percentages = [];
        foreach ($values as $value) {
            $percentages[] = ($value / $total) * 100;
        }

        return $twig->render(
            '_functions/gauge/linear.svg.twig',
            [
                'values' => $values,
                'percentages' => $percentages,
                'total' => $total,
            ]
        );
    }

    /**
     * @param Environment $twig
     * @param mixed ...$values
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function gaugeRadial(Environment $twig, ...$values)
    {
        // From the css, used to calculate the radius
        $strokeWidth = 90;
        $size = 360;
        $center = $size / 2;
        $r = ($size / 2) - ($strokeWidth / 2);
        // Assume a percentage if only one value passed.
        $total = count($values) > 1 ? array_sum($values) : 100;

        // Calculate the arcs
        $paths = [];
        $previousAngle = 0.0;
        foreach ($values as $value) {
            $endAngle = ($value / $total) * 360;
            $paths[] = $this->describeSvgArc($center, $center, $r, $previousAngle, $endAngle);
            $previousAngle = $endAngle;
        }

        return $twig->render(
            '_functions/gauge/radial.svg.twig',
            [
                'values' => $values,
                'total' => $total,
                'paths' => $paths,
                'size' => $size,
                'stroke_width' => $strokeWidth,
                'r' => $r,
                'center' => $center,
            ]
        );
    }

    /**
     * @param int $cx
     * @param int $cy
     * @param int $r
     * @param float $startAngle
     * @param float $endAngle
     *
     * @return string
     *   The "d" attribute for an SVG path
     */
    private function describeSvgArc(int $cx, int $cy, int $r, float $startAngle, float $endAngle): string
    {
        $startPoint = $this->polarToCartesian($cx, $cy, $r, $endAngle);
        $endPoint = $this->polarToCartesian($cx, $cy, $r, $startAngle);
        $svgLargeArcFlag = $endAngle - $startAngle <= 180 ? 0 : 1;

        // SVG path "d" attribute
        $d = implode(
            ' ',
            [
                'M',
                $startPoint['x'],
                $startPoint['y'],
                'A',
                $r,
                $r,
                0,
                $svgLargeArcFlag,
                0,
                $endPoint['x'],
                $endPoint['y'],
            ]
        );

        return $d;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $r
     * @param float $angle
     *
     * @return array
     */
    private function polarToCartesian(int $x, int $y, int $r, float $angle): array
    {
        $angle = deg2rad($angle);

        return [
            'x' => $x + ($r * cos($angle)),
            'y' => $y + ($r * sin($angle)),
        ];
    }
}

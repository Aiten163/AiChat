<?php

namespace App\Orchid\Layouts\Analytics;

use Orchid\Screen\Layouts\Chart;

class MessagesLineChart extends Chart
{
    /**
     * Height of the chart.
     *
     * @var int
     */
    protected $height = 300;

    /**
     * Configuring line.
     *
     * @var array
     */
    protected $lineOptions = [
        'spline'     => 1,
        'regionFill' => 1,
        'hideDots'   => 0,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 3,
    ];

    /**
     * Add a color to the chart.
     *
     * @var string|array
     */
    protected $colors = [
        '#3578e5',
    ];
}

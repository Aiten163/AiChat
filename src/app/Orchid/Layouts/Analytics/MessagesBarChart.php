<?php

namespace App\Orchid\Layouts\Analytics;

use Orchid\Screen\Layouts\Chart;

class MessagesBarChart extends Chart
{
    /**
     * Chart type
     *
     * @var string
     */
    protected $type = self::TYPE_BAR;

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
    protected $barOptions = [
        'horizontal'  => false,
        'distributed' => false,
        'endingShape' => 'flat',
        'columnWidth' => '70%',
    ];

    /**
     * Add a color to the chart.
     *
     * @var string|array
     */
    protected $colors = [
        '#28a745',
    ];
}

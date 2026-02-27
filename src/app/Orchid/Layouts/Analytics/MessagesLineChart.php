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
     * Chart title.
     *
     * @var string
     */
    protected $title = 'Количество сообщений по дням';

    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'chart';

    /**
     * Configuring line.
     *
     * @var array
     */
    protected $lineOptions = [
        'spline'     => 0,
        'regionFill' => 1,
        'hideDots'   => 0,
        'hideLine'   => 0,
        'heatline'   => 0,
        'dotSize'    => 3,
    ];

    /**
     * Color lines.
     *
     * @var string
     */
    protected $color = '#2C5CC3';

    /**
     * To highlight certain values on the Y axis, markers can be set.
     * They will shown as dashed lines on the graph.
     */
    protected function markers(): ?array
    {
        return [
            [
                'label'   => 'Medium',
                'value'   => 40,
            ],
        ];
    }
}

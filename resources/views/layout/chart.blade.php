{!! preg_replace(
    '/"@@([a-z_]+)@@"/',
    'ChartCallbacks.$1',
    $chart->render()
) !!}

{!! preg_replace(
    '/"@@([a-z_]+)@@"/',
    '$1',
    $chart->render()
) !!}

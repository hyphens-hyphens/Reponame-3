<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Log Kim Yến Kéo Xe</title>
</head>
<body>
<p>Server: <b>S{{ $mainAcc['jx_server'] }}</b> , Thời gian: <b>{{ date('d-m-Y H:i:s', $mainAcc['enter_at']) }}</b>.</p>
<p>Acc chính: <b>{{ $mainAcc['user'] }}</b> level {{ $mainAcc['level'] }}. <b>{{ \T2G\Common\Util\CommonHelper::getFilteredHwid($mainAcc['hwid']) }}</b></p>
<p>Map: <b>{{ $mainAcc['map_name'] }} ({{ $mainAcc['map_id'] }}) -> {{ $mainAcc['move_map_name'] }} ({{ $mainAcc['move_map_id'] }})</b>.</p>
@php
$colors = ['red', 'blue', 'green', 'orange', 'teal', 'violet', 'pink', 'brown', 'chocolate', 'darkcyan', 'crimson'];
$hwidColors = [];
@endphp
<p>HWIDs:</p>
<ul>
@foreach ($hwidArray as $index => $hwid)
    @php
        $hwidColors[$hwid] = $colors[$index];
    @endphp
    <li><span style="color: {{ $colors[$index] }}">{{ $hwid }}</span></li>
@endforeach
</ul>
<p>Dàn acc:</p>
<ul>
@php
    $sortingAccs = [];
    foreach ($secondaryAccs as $index => $acc) {
        $hwid = \T2G\Common\Util\CommonHelper::getFilteredHwid($acc['hwid']);
        $sortingAccs[$hwid] = $acc;
    }
    ksort($sortingAccs);
@endphp
@foreach ($sortingAccs as $index => $acc)
    @php
    $hwid = \T2G\Common\Util\CommonHelper::getFilteredHwid($acc['hwid']);
    @endphp
    <li>
        <b>{{ $acc['user'] }} ({{ $acc['char'] }})</b>,
        <span style="color:{{ $hwidColors[$hwid] }}">{{ $hwid }}</span>,
        <b>{{ $acc['weight'] }} lần</b>
    </li>
@endforeach
</ul>
</body>
</html>

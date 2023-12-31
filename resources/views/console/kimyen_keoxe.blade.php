@extends('t2g_common::console.layout')

@section('title')
    Log Kim Yến Kéo Xe
@endsection
@section('content')
    <p>Server: <b>S{{ $mainAcc['jx_server'] }}</b> , Thời gian: <b>{{ date('d-m-Y H:i:s', $mainAcc['enter_at']) }}</b>.</p>
    <p>Map: <b>{{ $mainAcc['map_name'] }} ({{ $mainAcc['map_id'] }}) -> {{ $mainAcc['move_map_name'] }} ({{ $mainAcc['move_map_id'] }})</b>.</p>
    <p>Dàn acc:</p>
    @php
        $sortingAccs = $usernames = [];
        foreach ($listAccs as $index => $acc) {
            $hwid = \T2G\Common\Util\CommonHelper::getFilteredHwid($acc['hwid']);
            $sortingAccs[$hwid . $index] = $acc;
        }
        ksort($sortingAccs);
    @endphp
    <table>
        <thead>
        <tr>
            <th>HWID</th>
            <th>Số lần</th>
            <th>Username</th>
            <th>Char</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($sortingAccs as $acc)
            @php
                $usernames[] = $acc['user'];
            @endphp
            <tr>
                <td>@include('t2g_common::console.partials.hwid', ['hwid' => $acc['hwid']])</td>
                <td>{{ $acc['weight'] > 0 ? $acc['weight'] : "Acc Chính" }}</td>
                <td>{{ $acc['user'] }}</td>
                <td>{{ $acc['char'] }}, LV {{ $acc['level'] }}</td>
                <td>{{ !empty($ips[$acc['user']]) ? implode(' - ', $ips[$acc['user']]) : '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p><a href="{{ route('voyager.console_log_viewer.hwid', ['d' => 1, 'u' => implode(',', $usernames), 's' => $mainAcc['jx_server']]) }}" target="_blank">Xem thêm</a></p>
@endsection


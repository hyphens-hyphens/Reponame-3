@extends('t2g_common::console.layout')

@section('title')
    Alert Multi Login
@endsection

@section('content')
    @php
    $item = $accs[0];
    @endphp
    <p>Server: <b>S{{ $server }}</b></p>
    <p>File: <b>{{ $item['log']['file']['path'] }}</b></p>
    <p>HWID: <b>{{ $hwidFiltered ?? '' }}</b></p>
    <p>Thời gian: <b>{{ (new \DateTime($item['@timestamp']))->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') }}</b></p>
    <p>Dàn acc:</p>
    @php
        $sortingAccs = $usernames = [];
        foreach ($accs as $index => $acc) {
            $userHwid = $hwids[$acc['user']] ?? null;
            if (!$userHwid) {
                continue;
            }
            $hwid = \T2G\Common\Util\CommonHelper::getFilteredHwid($userHwid);
            $sortingAccs[$hwid . $index] = $acc;
        }
        ksort($sortingAccs);
    @endphp
    <table>
        <thead>
        <tr>
            <th>HWID</th>
            <th>IP LAN</th>
            <th>Username</th>
            <th>Char</th>
            <th>Map</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($sortingAccs as $acc)
            @php
                $usernames[] = $acc['user'];
                $userHwid = $hwids[$acc['user']] ?? null;
            @endphp
            <tr>
                <td>@include('t2g_common::console.partials.hwid', ['hwid' => $userHwid])</td>
                <td>{{ !empty($ips[$acc['user']]) ? implode(' - ', $ips[$acc['user']]) : '' }}</td>
                <td>{{ $acc['user'] }}</td>
                <td>{{ $acc['char'] }}, LV {{ $acc['level'] }}</td>
                <td>{{ $acc['map'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

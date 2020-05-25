@extends('t2g_common::console.layout')

@section('title')
    Alert Multi Login
@endsection

@section('content')
    <p>Server: <b>S{{ $server }}</b> , Thời gian: <b>{{ date('d-m-Y H:i:s') }}</b>.</p>
    <p>Dàn acc:</p>
    @php
        $sortingAccs = $usernames = [];
        foreach ($accs as $index => $acc) {
            $hwid = \T2G\Common\Util\CommonHelper::getFilteredHwid($acc['hwid']);
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
            @endphp
            <tr>
                <td>@include('t2g_common::console.partials.hwid', ['hwid' => $acc['hwid']])</td>
                <td>{{ !empty($ips[$acc['user']]) ? implode(' - ', $ips[$acc['user']]) : '' }}</td>
                <td>{{ $acc['user'] }}</td>
                <td>{{ $acc['char'] }}, LV {{ $acc['level'] }}</td>
                <td>{{ $acc['map'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

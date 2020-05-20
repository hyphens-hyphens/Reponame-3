@extends('t2g_common::console.layout')
@section('title')
    Log HWID
@endsection

@section('content')
    <h2 class="text-center">Tra Log HWID</h2>
    <form action="" method="GET">
        <div class="input-group">
            <label for="d">Số ngày truy</label>
            <input type="text" name="d" id="d" value="{{ request('d', 10) }}">
        </div>
        <div class="input-group">
            <label for="s">Server</label>
            <input type="text" name="s" id="s" value="{{ request('s') }}">
        </div>
        <div class="input-group">
            <label for="u">Nhập username để xem log đăng nhập, phân cách bởi dấu <b>,</b></label>
            <input type="text" name="u" id="u" value="{{ request('u') }}" placeholder="VD: admin01,admin02">
            <button type="submit">Search</button>
        </div>
    </form>
    @if(count($results))
        <table>
            <thead>
            <tr>
                <th>Username</th>
                <th>HWID</th>
                <th>Time</th>
                <th>Server</th>
                <th>Char</th>
                <th>Level</th>
            </tr>
            </thead>
            <tbody>
            @foreach($results as $username => $hwids)
                <tr>
                    <td rowspan="{{ count($hwids) + 1 }}"><h3>{{ $username }}</h3></td>
                </tr>
                @foreach($hwids as $row)
                    <tr>
                        <td>@include('t2g_common::console.partials.hwid', ['hwid' => $row['hwid']])</td>
                        <td>{{ $row['time']->setTimezone(new \DateTimeZone(config('app.timezone')))->format('d-m-Y H:i:s') }}</td>
                        <td>S{{ $row['server'] }}</td>
                        <td>{{ $row['char'] }}</td>
                        <td>{{ $row['level'] }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    @endif
@endsection

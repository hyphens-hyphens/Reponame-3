<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Log HWID</title>
    <style>
        table {
            border-spacing: 1;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            position: relative;
        }
        table * {
            position: relative;
        }
        table th {
            text-align: center;
        }
        table td, table th {
            padding-left: 8px;
        }
        table thead tr {
            height: 60px;
            background: #FFED86;
            font-size: 16px;
        }
        table tbody tr {
            height: 24px;
            border-bottom: 1px solid #E3F1D5;
        }
        table tbody tr:last-child {
            border: 0;
        }
        table td{
            text-align: left;
        }
        table td.l, table th.l {
            text-align: right;
        }
        table td.c, table th.c {
            text-align: center;
        }
        table td.r, table th.r {
            text-align: center;
        }

        @media screen and (max-width: 35.5em) {
            table {
                display: block;
            }
            table > *, table tr, table td, table th {
                display: block;
            }
            table thead {
                display: none;
            }
            table tbody tr {
                height: auto;
                padding: 8px 0;
            }
            table tbody tr td {
                padding-left: 45%;
                margin-bottom: 12px;
            }
            table tbody tr td:last-child {
                margin-bottom: 0;
            }
            table tbody tr td:before {
                position: absolute;
                font-weight: 700;
                width: 40%;
                left: 10px;
                top: 0;
            }
            table tbody tr td:nth-child(1):before {
                content: "Code";
            }
            table tbody tr td:nth-child(2):before {
                content: "Stock";
            }
            table tbody tr td:nth-child(3):before {
                content: "Cap";
            }
            table tbody tr td:nth-child(4):before {
                content: "Inch";
            }
            table tbody tr td:nth-child(5):before {
                content: "Box Type";
            }
        }
        body {
            background: #9BC86A;
            font: 400 14px 'Calibri','Arial';
            padding: 20px;
        }
        form {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding-bottom: 20px;
        }
        blockquote {
            color: white;
            text-align: center;
        }
        .input-group {
            display: flex;
            align-content: stretch;
        }
        .input-group label {
            text-align: right;
            width: 40%;
        }
        .input-group > input {
            flex: 1 0 auto;
            padding: 8px;
            font-size: 14px;
        }

        /**
         * Even when I set some dimension-related styles on this
         * element but not on the input or button, they'll still
         * line up.
         */
        .input-group-addon {
            background: #eee;
            border: 1px solid #ccc;
            padding: 0.5em 1em;
        }
        label {
            font-size: 16px;
            margin-top: 10px;
            padding-right: 10px;
        }
    </style>
</head>
<body>
    <form action="" method="GET">
        <div class="input-group">
            <label for="d">Số ngày truy</label>
            <input type="text" name="d" id="d" value="{{ request('d', 10) }}">
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
                <td>{{ $row['hwid'] }}</td>
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
</body>
</html>

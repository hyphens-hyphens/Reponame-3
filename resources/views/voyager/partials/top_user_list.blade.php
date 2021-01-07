<table class="table rank-list-content">
    <tbody>
    <thead class="thead-dark">
    <tr>
        <th>STT</th>
        <th>User</th>
        <th>Chars</th>
        <th>Level</th>
        <th>Exp</th>
    </tr>
    </thead>
    <div class="total-items">
        show <span>{{ $data->firstItem() }} - {{ $data->lastItem() }}</span> of <span>{{ $data->total() }}</span>
    </div>
    @foreach($data as $k => $row)
        <tr>
            <td><span style="margin-left: -4px;" class="voyager-angle-right"></span>{{ $k + 1 }}</td>
            <td>{{ $row['user'] }}</td>
            <td>{{ $row['char'] }}</td>
            <td>{{ $row['level'] }}</td>
            <td>{{ number_format($row['exp'], 0, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $data->links() }}
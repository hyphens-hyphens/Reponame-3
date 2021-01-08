<table class="table rank-list-content">
    <tbody>
    <thead class="thead-dark">
    <tr>
        <th>STT</th>
        <th>User</th>
        <th>Char</th>
        <th>Level</th>
        <th>Exp</th>
    </tr>
    </thead>
    @foreach($data as $k => $row)
        <tr>
            <td>{{ $data->firstItem() + $k }}</td>
            <td>{{ $row['user'] }}</td>
            <td>{{ $row['char'] }}</td>
            <td>{{ $row['level'] }}</td>
            <td>{{ number_format($row['exp'], 0, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $data->links() }}

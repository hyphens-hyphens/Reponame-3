{{--user revisions--}}
<div class="panel panel panel-bordered panel-warning">
    <div class="panel-body">
        <h4 class="title">Hoạt động gần đây</h4>
        <table class="table table-hover dataTable no-footer" role="grid" aria-describedby="dataTable_info">
            <tr>
                <th>Thời gian</th>
                <th>Thông tin thay đổi</th>
                <th>Giá trị cũ</th>
                <th>Giá trị mới</th>
                <th>Người thực hiện</th>
            </tr>
            @foreach($dataTypeContent->advancedRevisionHistory as $history )
                <tr>
                    <td>{{ $history->created_at->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $history->fieldName() }}</td>
                    <td>{{ $history->oldValue() }}</td>
                    <td>{{ $history->newValue() }}</td>
                    <td>{{ $history->userResponsible() ? $history->userResponsible()->name : '' }}</td>
                </tr>
            @endforeach
            @if(count($dataTypeContent->revisionHistory) == 0)
                <tr>
                    <td colspan="5">Không có lịch sử hoạt động</td>
                </tr>
            @endif
        </table>
    </div>
</div>

<div class="col-md-7">
    <div class="panel panel panel-bordered panel-warning">
        <div class="panel-body">
            <h4 class="title">Lịch sử giao dịch</h4>
            <p class="h4">Đã nạp: <span class="label h5 label-success">{{ number_format($dataTypeContent->getTotalPaid()) }}</span></p>
            @if($debt = $dataTypeContent->getTotalDebt())
                <p class="h4">Đang ứng: <span class="label h5 label-danger">{{ number_format($debt) }}</span></p>
            @endif
            <table class="table table-hover dataTable no-footer" role="grid" aria-describedby="dataTable_info">
                <tr>
                    <th>ID</th>
                    <th>Thông tin</th>
                    <th>Số tiền</th>
                    <th>Số Xu</th>
                    <th>Tình trạng</th>
                </tr>
                @foreach($histories as $history)
                    <tr>
                        <td>{{ $history->id }}</td>
                        <td>{!! view('vendor.voyager.payments.transaction_id', ['data' => $history]) !!}</td>
                        <td>{{ number_format($history->amount) }}</td>
                        <td>{{ number_format($history->gamecoin) }}</td>
                        <td>{!! $history->getStatusText(true) !!}</td>
                    </tr>
                @endforeach
                @if($histories->total() == 0)
                    <tr>
                        <td colspan="5">Không có lịch sử giao dịch</td>
                    </tr>
                @endif
            </table>
            @if($histories->total() > 0)
                <div class="center">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

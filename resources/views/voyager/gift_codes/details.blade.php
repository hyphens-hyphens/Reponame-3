<div class="col-xs-6">
    <div class="panel panel panel-bordered panel-warning">
        <div class="panel-body">
            <h4 class="title">Danh sách Gift Code</h4>
            <p >Số lượng
                <span class="label h5 label-danger" data-toggle="tooltip" title="Đã sử dụng">{{ number_format($dataTypeContent->getNumberOfUsedCodes()) }}</span> /
                <span class="label h5 label-success" data-toggle="tooltip" title="Tổng số">{{ number_format($codes->total()) }}</span>
            </p>
            <div class="row mt5">
                <div class="col-xs-12">
                    <p>Tìm kiếm</p>
                    <form method="get" class="form-search">
                        <div id="search-input">
                            <select id="search_key" name="key">
                                <option value="user" @if(request('key') == 'user') selected="selected" @endif>User sử dụng</option>
                                <option value="issued_for" @if(request('key') == 'issued_for') selected="selected" @endif>User được cấp</option>
                                <option value="code" @if(request('key') == 'code') selected="selected" @endif>Code</option>
                            </select>
                            <div class="input-group col-md-12">
                                <input type="text" class="form-control" placeholder="Search" name="s" value="{{ request('s') }}">
                                <span class="input-group-btn">
                                <button class="btn btn-info btn-lg" type="submit">
                                    <i class="voyager-search"></i>
                                </button>
                            </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if($codes->total() > 0)
                <div class="center">
                    {{ $codes->links() }}
                </div>
            @endif
            <br>
            <table class="table table-hover dataTable no-footer" role="grid" aria-describedby="dataTable_info">
                <tr>
                    <th>Code</th>
                    <th>User sử dụng</th>
                    <th>User được cấp</th>
                    <th>Thời gian</th>
                </tr>
                @foreach($codes as $code)
                    <tr>
                        <td><code class="text-primary">{{ $code->code }}</code></td>
                        <td>{{ $code->owner->name ?? '' }}</td>
                        <td>{{ $code->issuedFor->name ?? '' }}</td>
                        <td>{{ $code->updated_at ? $code->updated_at->format('Y-m-d H:i:s') : '' }}</td>
                    </tr>
                @endforeach
                @if($codes->total() == 0)
                    <tr>
                        <td colspan="3">Không có gift code</td>
                    </tr>
                @endif
            </table>
            <br>
            @if($codes->total() > 0)
                <div class="center">
                    {{ $codes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

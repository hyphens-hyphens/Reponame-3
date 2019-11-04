<div class="collapsible well">
    <form method="GET" class="form-search">
        <input type="hidden" name="search" value="1">
        <div class="collapse-head collapsed">
            <h4>Search</h4>
            <div class="input-group">
                <input class="form-control" name="keyword" type="text"
                       value="{{ request('keyword') }}"
                       placeholder="Tìm theo mã thẻ hoặc seri thẻ" aria-label="Search">
                <div class="input-group-btn">
                    <button class="btn btn-primary" type="submit">
                        <i class="voyager-icon voyager-search"></i>
                    </button>
                    <button class="btn {{ request('search') ? 'voyager-double-up' : 'collapsed voyager-double-down' }} "
                            type="button" data-toggle="collapse"
                            data-target="#searchForm" aria-controls="searchForm"
                            data-up="voyager-double-up" data-down="voyager-double-down">
                    </button>
                </div>
            </div><!-- /input-group -->
        </div>
        <div class="collapse-content {{ request('search') ? 'in' : 'collapse' }}" id="searchForm">
            <div class="mt10"></div>
            <div class="row">
                <div class="form-search-content">
                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="id">Payment ID</label>
                        <input type="text" class="form-control" name="id"
                               value="{{ request('id') }}"/>
                    </div>
                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="id">Note</label>
                        <input type="text" class="form-control" name="note"
                               value="{{ request('note') }}"/>
                    </div>

                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="created_at">Thời gian</label>
                        <input type="text" class="form-control" name="created_at"
                               placeholder="Ex: YYYY-mm-dd, > 2019-09, < 2019-09, < 2019-08-30"
                               value="{{ request('created_at') }}"/>
                    </div>

                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="user_id">Tài khoản</label>
                        <select class="form-control select2-users" name="user_id">
                            @if($userId = request('user_id') && $user = \App\User::find(request('user_id')))
                                <option selected="selected" value="{{ $userId }}">{{ $user->name }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="pay_method">Loại giao dịch</label>
                        <select class="form-control select2" name="pay_method">
                            <option value="">Tất cả</option>
                            @foreach($payMethods as $text)
                                <option {{ request('pay_method') == $text ? 'selected="selected"' : '' }} value="{{ $text }}">{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="status_code">Trạng thái</label>
                        <select class="form-control select2" name="status_code">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $code)
                                <option {{ request('status_code') == $code ? 'selected="selected"' : '' }} value="{{ $code }}">
                                    {{ strip_tags(\T2G\Common\Models\Payment::displayStatus($code, true, false)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-xs-12 col-sm-4">
                        <label for="pay_from">Ngân hàng</label>
                        <select class="form-control select2" name="pay_from">
                            <option value="">Tất cả</option>
                            @php
                                $banks = ['Đông Á', 'Vietcombank']
                            @endphp
                            @foreach($banks as $bank)
                                <option {{ request('pay_from') == $bank ? 'selected="selected"' : '' }} value="{{ $bank }}">{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-12">
                    <a href="{{ request()->getPathInfo() }}" class="btn btn-warning clear">Clear</a>
                    <button type="submit" class="btn btn-primary save">Search</button>
                </div>
            </div>
        </div><!-- panel-body -->
    </form>
</div>
@push('extra-js')
    <script>
        $(document).ready(function () {
            $('.form-search .select2-users').select2({
                allowClear: true,
                placeholder: "Tất cả",
                width: '100%',
                ajax: {
                    url: '{{ route('autocomplete.users') }}',
                }
            });
        });
    </script>
@endpush

<div class="table-responsive hidden-xs">
    <table id="dataTable" class="table table-hover">
        <thead>
        <tr>
            @can('delete',app($dataType->model_name))
                <th>
                    <input type="checkbox" class="select_all">
                </th>
            @endcan
            @foreach($dataType->browseRows as $row)
                <th>
                    @if ($isServerSide)
                        <a href="{{ $row->sortByUrl() }}">
                            {{ $row->display_name }}
                            @if ($row->isCurrentSortField())
                                @if (!isset($_GET['sort_order']) || $_GET['sort_order'] == 'asc')
                                    <i class="voyager-angle-up pull-right"></i>
                                @else
                                    <i class="voyager-angle-down pull-right"></i>
                                @endif
                            @endif
                        </a>
                    @else
                        {{ $row->display_name }}
                    @endif
                </th>
            @endforeach
            <th class="actions text-right">{{ __('voyager::generic.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dataTypeContent as $data)
            <tr>
                @can('delete',app($dataType->model_name))
                    <td>
                        <input type="checkbox" name="row_id" id="checkbox_{{ $data->getKey() }}" value="{{ $data->getKey() }}">
                    </td>
                @endcan
                @foreach($dataType->browseRows as $row)
                    <td>
                        {!! voyager()->displayField($dataType, $row, $data) !!}
                    </td>
                @endforeach
                <td class="no-sort no-click" id="bread-actions">
                    @foreach(voyager()->actions() as $action)
                        @include(voyager()->getBreadView('partials.actions', $dataType->slug), ['action' => $action])
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="table-xs hidden visible-xs">
    <table id="dataTable" class="table table-bordered">
        <thead>
        <tr>
            @foreach($dataType->browseRows as $row)
                @if (in_array($row->field, ['id', 'transaction_id', 'status']))
                <th>
                    <a href="{{ $row->sortByUrl() }}">
                        {{ $row->display_name }}
                        @if ($row->isCurrentSortField())
                            @if (!isset($_GET['sort_order']) || $_GET['sort_order'] == 'asc')
                                <i class="voyager-angle-up pull-right"></i>
                            @else
                                <i class="voyager-angle-down pull-right"></i>
                            @endif
                        @endif
                    </a>
                </th>
                @endif
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($dataTypeContent as $k => $data)
            <tr class="xs-info {{ $k % 2 == 0 ? 'default' : 'active' }}">
                @foreach($dataType->browseRows as $row)
                    @if (in_array($row->field, ['id', 'transaction_id']))
                    <td>
                        {!! voyager()->displayField($dataType, $row, $data) !!}
                    </td>
                    @endif
                    @if ($row->field == 'status')
                        <td>
                            {!! voyager()->displayField($dataType, $row, $data) !!}
                            <p>Thời gian: {{ $data->created_at->format('d-m-Y H:i') }}</p>
                            <p>Username: @include('t2g_common::voyager.payments.username')</p>
                            <p>Số xu: @include('t2g_common::voyager.payments.gamecoin')</p>
                        </td>
                    @endif
                @endforeach
            </tr>
            <tr class="xs-actions {{ $k % 2 == 0 ? 'default' : 'active' }}">
                <td>
                    <b>Actions:</b>
                </td>
                <td colspan="2" class="no-sort no-click bread-actions">
                    @foreach(array_reverse(voyager()->actions()) as $action)
                        @include('t2g_common::voyager.payments.partials.actions-xs', ['action' => $action])
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if ($isServerSide)
    <div class="pull-left">
        <div role="status" class="show-res" aria-live="polite">
            {{ trans_choice(
                'voyager::generic.showing_entries', $dataTypeContent->total(), [
                    'from' => $dataTypeContent->firstItem(),
                    'to' => $dataTypeContent->lastItem(),
                    'all' => $dataTypeContent->total()
                ])
            }}
        </div>
    </div>
    <div class="pull-right">
        {{ $dataTypeContent->appends(request()->all())->links() }}
    </div>
@endif
<style>
    .table-xs .xs-info td {
        padding-top: 15px;
    }
    .table-xs .xs-actions td {
        padding-bottom: 15px;
    }
    .table-xs .xs-actions td {
        border-top: none;
        vertical-align: middle;
    }
    .table-xs .xs-actions.bread-actions {
        text-align: center;
    }
    .table-xs .bread-actions a {
        padding: 8px 16px;
        font-size: 18px !important;
        margin: 0 8px !important;
        float: none !important;
    }
    .table-xs .bread-actions a i {
        font-size: 18px !important;
    }
</style>

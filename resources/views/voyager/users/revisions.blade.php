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
                @can('edit', $dataTypeContent)
                <th>Thao tác nhanh</th>
                @endcan
            </tr>
            @foreach($dataTypeContent->advancedRevisionHistory as $history )
                <tr>
                    <td>{{ $history->created_at->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $history->fieldName() }}</td>
                    <td>{{ $history->oldValue() }}</td>
                    <td>{{ $history->newValue() }}</td>
                    <td>{{ $history->userResponsible() ? $history->userResponsible()->name : '' }}</td>
                    @can('edit', $dataTypeContent)
                    <td class="row-action">
                        <button class="btn btn-warning btn-revision-revert"
                                data-id="{{ $history->id }}"
                                data-field="{{ $history->fieldName() }}"
                                data-value="{{ $history->oldValue() }}"
                        >
                            <i class="icon voyager-refresh"></i> Phục Hồi
                        </button>
                    </td>
                    @endcan
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

{{-- Single revert modal --}}
<div class="modal modal-warning fade" tabindex="-1" id="revert_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="voyager-warning"></i> Bạn có xác định phục hồi thay đổi này?</h4>
            </div>
            <div class="modal-body">
                Phục hồi thông tin <code class="revert-field"></code> thành <code class="revert-value"></code>
            </div>
            <div class="modal-footer">
                <form action="{{ route('voyager.users.revision_revert') }}" id="revert_form" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" name="user_id" value="{{ $dataTypeContent->id }}">
                    <input type="hidden" name="revision_id">
                    <button type="submit" class="btn btn-primary pull-right delete-confirm">
                        <i class="voyager-refresh"></i> Phục hồi
                    </button>
                </form>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@push('extra-js')
    <script>
        $(document).ready(function () {
            $('.btn-revision-revert').click(function (e) {
                let data = $(this).data();
                console.log(data);
                $('#revert_form').find('input[name="revision_id"]').val(data.id);
                let $modal = $('#revert_modal');
                $modal.find('.modal-body .revert-field').html(data.field);
                $modal.find('.modal-body .revert-value').html(data.value);
                $modal.modal('show');
            });
        });
    </script>
@endpush

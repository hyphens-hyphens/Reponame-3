@can('editPassword', $data)
    <a href="javascript:;" class="editable" data-name="password" data-type="text" data-title="Mật khẩu cấp 1"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->getRawPassword() }}</a>
@endcan

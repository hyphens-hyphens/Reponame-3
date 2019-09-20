@can('editPassword', $data)
    <a href="javascript:;" class="editable" data-name="password2" data-type="text" data-title="Mật khẩu cấp 2"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->getRawPassword2() }}</a>
@endcan

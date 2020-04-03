<p>
    Name: <a href="javascript:" class="editable" data-name="group_name" data-type="text" data-title="Group Name"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->group_name }}</a>
</p>
<p>
    Title: <a href="javascript:" class="editable" data-name="group_title" data-type="text" data-title="Group Title"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->group_title }}</a>
</p>
<p>
    Order: <a href="javascript:" class="editable" data-name="group_order" data-type="number" data-title="Group Order"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->group_order }}</a>
</p>
<p>
    Parent's Name: <a href="javascript:" class="editable" data-name="group_sub" data-type="text" data-title="Parent Group Post"
       data-pk="{{ $data->id }}" data-url="{{ route('voyager.' . $dataType->slug . '.quickEdit') }}">{{ $data->group_sub }}</a>
</p>

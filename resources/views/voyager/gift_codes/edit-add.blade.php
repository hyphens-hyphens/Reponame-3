@extends('voyager::bread.edit-add')
@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-{{ empty($dataTypeContent->id) ? 12 : 6 }}">
                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                          class="form-edit-add"
                          action="@if(!is_null($dataTypeContent->getKey())){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
                          method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                    @if(!is_null($dataTypeContent->getKey()))
                        {{ method_field("PUT") }}
                    @endif

                    <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="form-group col-md-12">
                                <label for="name">Name</label>
                                <input required="" type="text" class="form-control" name="name" placeholder="Name" value="{{ $dataTypeContent->name ?? '' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="prefix">Code Prefix</label>
                                <input required="" type="text" class="form-control" name="prefix" placeholder="Code Prefix" value="{{ $dataTypeContent->prefix ?? '' }}"
                                    @if(!empty($dataTypeContent->id))
                                        readonly="readonly"
                                    @endif
                                >
                            </div>
                            @if(\Auth::user()->hasRole('admin'))
                            <div class="form-group col-md-12">
                                <label for="code_name">Code Name</label>
                                <input required="" type="text" class="form-control" name="code_name" placeholder="Code Name" value="{{ $dataTypeContent->code_name ?? '' }}">
                            </div>
                            @endif
                            @foreach($dataTypeRows as $row)
                                @if($row->field == 'type')
                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ isset($display_options->width) ? $display_options->width : 12 }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    <label for="name">{{ $row->display_name }}</label>
                                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="quantity">Số lượng
                                        @if(!empty($dataTypeContent->id))
                                            <code>Nhập để add thêm code</code>
                                        @endif
                                    </label>
                                    <input type="text" class="form-control" name="quantity" placeholder="Số lượng" value="">
                                </div>
                                @endif
                            @endforeach

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            @if($dataTypeContent->id && $dataTypeContent->type == \T2G\Common\Models\GiftCode::TYPE_PER_ACCOUNT)
                @include('t2g_common::voyager.gift_codes.details')
            @endif
        </div>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

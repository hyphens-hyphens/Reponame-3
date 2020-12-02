<div class="panel panel-transparent panel-bordered">
    <div class="panel-heading">
        <h3 class="panel-title panel-icon">
            <i class="voyager-plus"></i> Add Code Like Share
        </h3>
    </div>
    <div class="panel-body">
        <form role="form" action="{{ route('voyager.gift_code.add_code') }}" method="POST">
            <!-- PUT Method if we are editing -->
            {{ csrf_field() }}
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session()->has('messages'))
                <div class="alert alert-info">
                    <ul>
                        @foreach (session()->get('messages') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="col-xs-12 col-sm-6">
                <div class="form-group col-xs-12">
                    <label for="code_id">Loại Code</label>
                    <div>
                        <select class="select2" name="code_id" id="code_id">
                            @foreach($codeTypes as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-xs-12">
                    <label for="username">Tên tài khoản</label>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Tên tài khoản" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group col-xs-12">
                    <label for="from">Bắt đầu từ</label>
                    <input type="text" class="form-control" name="from" id="from" placeholder="Nhập số bắt đầu của tài khoản">
                </div>
                <div class="form-group col-xs-12">
                    <label for="to">Kết thúc</label>
                    <input type="text" class="form-control" name="to" id="to" placeholder="Nhập số kết thúc của tài khoản">
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="col-xs-12">
                <div class="alert alert-info review-container" id="addCodePreview" style="display: none">
                </div>
            </div>

            <div class="panel-footer">
                <button type="submit" class="btn btn-primary save">Add</button>
            </div>
        </form>
    </div>

</div>

@push('extra-js')
    <script>
        $(document).ready(function () {
            var fromInput = $('#from');
            var toInput = $('#to');
            var $username = $('#username');
            var $code = $('#code_id');
            var $preview = $('#addCodePreview');
            function previewError() {
                $preview.html("Có lỗi xảy ra, kiểm tra lại dữ liệu nhập vào.").show();
            }
            function addCodePreview() {
                var code = $code.find('option:selected').text();
                var msg = `Add Code <b class="label label-success">${code}</b> cho tài khoản: `;
                var from = parseInt(fromInput.val());
                var to = parseInt(toInput.val());
                console.log(fromInput, from);
                console.log(toInput, to);
                var username = $username.val();
                if (!username) {
                    return $preview.hide();
                }
                if ((from.length && to.length) && from >= to) {
                    return previewError();
                }
                if (!from || !to) {
                    msg += `<b class="label label-warning">${username}</b>`;
                } else {
                    var listAccs = '';
                    for (var i = from; i <= to; i++) {
                        listAccs += `<li><b class="label label-warning">${username}${i}</b></li>`;
                    }
                    msg += `<ul>${listAccs}</ul>`;
                }
                $preview.html(msg).show();
            }

            $code.change(addCodePreview);
            $username.change(addCodePreview);
            $username.keyup(addCodePreview);
            fromInput.change(addCodePreview);
            fromInput.keyup(addCodePreview);
            toInput.change(addCodePreview);
            toInput.keyup(addCodePreview);
        });
    </script>
@endpush

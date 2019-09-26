@php
$rowClass = 'col-xs-12 col-md-4';
@endphp
<div class="panel panel-transparent panel-bordered">
    <div class="panel-heading">
        <h3 class="panel-title panel-icon">
            @if(isset($dataTypeContent->id))
                <i class="voyager-edit"></i> Edit payment <span class="label label-default">#{{ $dataTypeContent->id }}</span>
            @else
                <i class="voyager-plus"></i> Add Payment
            @endif
        </h3>
        @if(!empty($isBrowsing))
        <div class="panel-actions">
            <a class="btn panel-action {{ request('search') ? 'panel-collapsed voyager-double-down' : 'voyager-double-up' }}" data-up="voyager-double-up" data-down="voyager-double-down" data-toggle="panel-collapse" aria-hidden="true"></a>
        </div>
        @endif
    </div>
    <div class="panel-body" style="{{ !empty($isBrowsing) && request('search') ? 'display:none' : '' }}">
        <form role="form"
              class="form-edit-add"
              action="@if(empty($isBrowsing) && !is_null($dataTypeContent->getKey())){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
              method="POST" enctype="multipart/form-data">
            <!-- PUT Method if we are editing -->
            @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
            @endif
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
            @if(!empty($dataTypeContent->id))
                <p>
                    {!! $dataTypeContent->getStatusText(true) !!}
                    @if($dataTypeContent->gateway_response)
                        <span class="help-block text-danger">{{ $dataTypeContent->gateway_response }}</span>
                    @endif
                </p>
            @endif
            <div class="form-group {{ $rowClass }}">
                <label for="selectUser">Tài khoản</label>
                @if(empty($dataTypeContent->user_id))
                    <select required class="form-control select2-users" name="user_id" id="selectUser">
                        <option value="">Chọn User</option>
                    </select>
                @else
                    <input class="form-control" type="text" readonly="readonly" value="{{ $dataTypeContent->username }}">
                @endif
            </div>
            <div class="form-group {{ $rowClass }}">
                <label for="payment_type">Loại giao dịch</label>
                @if(!empty($dataTypeContent->id) && $dataTypeContent->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_CARD)
                    <input type="text" class="form-control" value="{{ $paymentTypes[$dataTypeContent->payment_type] ?? '' }}" readonly>
                @else
                    <select required class="form-control select2" name="payment_type" id="payment_type">
                        @if(empty($dataTypeContent->id))
                            <option value="">Chọn loại giao dịch</option>
                        @endif
                        @foreach($paymentTypes as $type => $text)
                            <option {{ old('payment_type', empty($isBrowsing) ? $dataTypeContent->payment_type : '') == $type ? 'selected="selected"' : '' }} value="{{ $type }}">{{ $text }}</option>
                        @endforeach
                    </select>
                @endif
                @if(empty($isBrowsing) && $dataTypeContent->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_CARD)
                    <p>
                        @include('partials.admin.card_info', ['item' => $dataTypeContent])
                    </p>
                @endif
            </div>
            <div class="form-group {{ $rowClass }}">
                <label for="support_fee">Phí support? (giao dịch acc, đổi ID...)</label>
                <br>
                <input type="checkbox" class="form-control" name="support_fee"
                       placeholder="" id="support_fee"
                       @if(!empty($dataTypeContent->id) && $dataTypeContent->isDone())
                       readonly="readonly"
                       @endif
                       @if(old('support_fee') || (!empty($dataTypeContent->id) && $dataTypeContent->gamecoin == 0))
                       checked="checked"
                        @endif
                />
            </div>

            <div class="form-group {{ $rowClass }}
            @if(!isset($dataTypeContent->payment_type) || $dataTypeContent->payment_type != \T2G\Common\Models\Payment::PAYMENT_TYPE_BANK_TRANSFER)
                    hidden
@endif
                    " id="bankWrapper">
                <label for="payment_type">Ngân hàng</label>
                <select required class="form-control select2" name="pay_from" id="pay_from">
                    @php
                        $banks = ['Đông Á', 'Vietcombank']
                    @endphp
                    @foreach($banks as $bank)
                        <option {{ old('pay_from', empty($isBrowsing) ? $dataTypeContent->pay_from : '') == $bank || empty($dataTypeContent->pay_from) ? 'selected="selected"' : '' }} value="{{ $bank }}">{{ $bank }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group {{ $rowClass }}">
                <label for="amount">Số tiền</label>
                <input required type="text" class="form-control" name="amount"
                       placeholder="" id="moneyAmount"
                       @if(!empty($dataTypeContent->id) && ($dataTypeContent->isDone() || $dataTypeContent->isInDebt()) )
                       readonly="readonly"
                       @endif
                       value="{{ $dataTypeContent->amount ?? old('amount') }}"/>
            </div>
            <div class="form-group {{ $rowClass }}">
                <label for="note">Ghi chú</label>
                <input type="text" class="form-control" name="note"
                       placeholder="" id="note"
                       value="{{ $dataTypeContent->note ??  old('note') }}"/>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12">
                <div class="alert alert-info hidden review-container">
                    <span class="h3" id="addGoldReview"></span> <=> <span class="h3"><span class="label label-success" id="moneyText"></span></span>
                </div>
            </div>

            <div class="panel-footer">
                @if(empty($isBrowsing))
                    <a href="{{ route('voyager.payments.index') }}" class="btn btn-default back">Back</a>
                @endif
                <button type="submit" class="btn btn-primary save">Save</button>
            </div>
        </form>
    </div>

</div>

@push('extra-js')
    <script>
        const PAYMENT_TYPE_MOMO = {{ \T2G\Common\Models\Payment::PAYMENT_TYPE_MOMO }};
        const PAYMENT_TYPE_BANK_TRANSFER = {{ \T2G\Common\Models\Payment::PAYMENT_TYPE_BANK_TRANSFER }};
        const PAYMENT_TYPE_ADVANCE_DEBT = {{ \T2G\Common\Models\Payment::PAYMENT_TYPE_ADVANCE_DEBT }};
        function addGoldReview() {
            let username = $('#selectUser :selected').text();
            let type = $('#payment_type').val();
            let gold = Math.round(parseInt($('#moneyAmount').val()) / {{ config('t2g_common.payment.game_gold.exchange_rate', 1000) }});
            if (type == PAYMENT_TYPE_MOMO || type == PAYMENT_TYPE_BANK_TRANSFER || type == PAYMENT_TYPE_ADVANCE_DEBT) {
                gold += gold * {{ config('t2g_common.payment.game_gold.bonus_rate', 10) / 100 }};
            }
            if (!username || !gold || !type) {
                $('.review-container').addClass('hidden');
                return;
            }
            let reviewText = 'Add vào tài khoản <b>' + username + '</b> <span class="label label-success">' + gold + ' Xu</span>';
            $('.review-container').removeClass('hidden');
            $('#addGoldReview').html(reviewText);
            $('#moneyText').html(moneyToText($('#moneyAmount').val()));
        }

        function toggleBankSelection() {
            let type = $(this).val();
            if (type == PAYMENT_TYPE_BANK_TRANSFER) {
                $('#bankWrapper').removeClass('hidden');
            } else {
                $('#bankWrapper').addClass('hidden');
            }
            addGoldReview();
        }

        let savingTimeout = null;
        $(document).ready(function () {
            $('#support_fee').bootstrapToggle({
                on: 'Yes',
                off: 'No'
            });
            $('#moneyAmount').change(addGoldReview);
            $('#moneyAmount').keyup(addGoldReview);
            $('#payment_type').change(toggleBankSelection);
            $('#selectUser').change(addGoldReview);
            $('form.form-edit-add').submit(function (e) {
                let saveBtn = $(this).find('.save');
                saveBtn.prop('disabled', 'disabled');
                savingTimeout = setTimeout(function () {
                    saveBtn.removeProp('disabled');
                }, 3000);
            });
            @if(!empty($isBrowsing) || !$dataTypeContent->user_id)
            $('#selectUser').select2({
                width: '100%',
                ajax: {
                    url: '{{ route('autocomplete.users') }}',
                }
            });
            @endif
        });

        function moneyToText(money, text, round) {
            money = parseInt(money);
            let divider = 1000;
            if (typeof round == 'undefined') {
                round = 0;
                while (Math.pow(divider, round + 1) <= money) {
                    round++;
                }
            }
            text = text || '';
            if (round == 0) {
                return text;
            }
            let roundText = '';
            switch (round) {
                case 3:
                    roundText = "Tỷ";
                    break;
                case 2:
                    roundText = "Triệu";
                    break;
                case 1:
                    roundText = "Ngàn";
                    break;
            }
            let comparator = Math.pow(divider, round);
            let roundUnit = Math.floor(money / comparator);
            if (roundUnit > 0) {
                text += " " + roundUnit + " " + roundText;
            }


            return moneyToText(money - (roundUnit * comparator), text, round - 1);
        }
    </script>
@endpush

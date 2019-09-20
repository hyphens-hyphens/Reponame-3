<input class="form-control input-daterange" type="text" placeholder="Từ ngày"
       value="{{ date("d-m-Y", strtotime($fromDate)) . " --- " . date("d-m-Y", strtotime($toDate)) }}"
       data-from="{{ $fromDate }}" data-to="{{ $toDate }}"
/>
<input type="hidden" name="fromDate" class="fromDate" value="{{ $fromDate }}"/>
<input type="hidden" name="toDate" class="toDate" value="{{ $toDate }}"/>

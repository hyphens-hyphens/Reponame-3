<div class="table table-responsive">
    <table class="table table-striped" id="tableUtmReport">
        <thead>
        <th>Campaign</th>
        <th>Source / Medium</th>
        <?php
        /** @var \DateTime $toDate */
        $data = $reportRegisteredByCampaign['data'];
        $campaigns = $reportRegisteredByCampaign['campaigns'];
        foreach ($dateArray as $date):
        $total = isset($data[$date]['total']) ? $data[$date]['total'] : 0;
        ?>
        <th class="source-total"><?= $date ?> <span class="label label-primary">{{ number_format($total) }}</span></th>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($campaigns as $campaign => $group) :
        if (count($campaigns[$campaign]) == 1):
        ?>
        <tr>
            <td><?php echo $campaign ?></td>
            <?php foreach ($campaigns[$campaign] as $group): ?>
            <td><?php echo str_replace('|', ' / ', $group) ?> <span class="label label-dark group-count"></span></td>
            <?php endforeach; ?>
            <?php foreach ($dateArray as $date):
            $key = "{$campaign}|{$group}";
            $total = isset($data[$date]['details'][$key]) ? $data[$date]['details'][$key] : 0;
            ?>
            <td class="number"><?php echo $total ?></td>
            <?php endforeach; ?>
        </tr>
        <?php else: ?>
        <tr><td rowspan="<?php echo count($campaigns[$campaign]) + 1 ?>"><?php echo $campaign ?></td></tr>
        <?php foreach ($campaigns[$campaign] as $group):
        ?>
        <tr>
            <td><?php echo str_replace('|', ' / ', $group) ?> <span class="label label-dark group-count"></span></td>
            <?php foreach ($dateArray as $date):
            $total = isset($data[$date]['details']["{$campaign}|{$group}"]) ? $data[$date]['details']["{$campaign}|{$group}"] : 0;
            ?>
            <td class="number"><?php echo $total ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>


@push('extra-js')
    <script>
        $('#tableUtmReport tbody tr').each(function (key, row) {
            let $row =  $(row);
            let rowTotal = 0;
            $row.find('td.number').each(function (k, cell) {
                rowTotal += parseInt($(cell).text());
            });
            $row.find('span.group-count').text(rowTotal);
        });
    </script>
@endpush

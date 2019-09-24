<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\Payment;

class UpdatePaymentProfitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:payment:update_profit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update new column `profit` for table `payments`';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perPage = 5;
        $offset = 0;
        $processed = 0;
        $this->output->text("Updating payments profit");
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = app(config('t2g_common.models.payment_model_class'));
        while (1) {
            $payments = $model->newQuery()->offset($offset)
                ->limit($perPage)
                ->orderBy('id', 'asc')
                ->get();

            if (!$payments->count()) {
                break;
            }
            $this->output->text("Processing with offset {$offset}");
            /** @var Payment $payment */
            foreach ($payments as $payment) {
                if ($payment->status) {
                    $profitRate = Payment::getProfitRate($payment->pay_method);
                    $payment->profit = $payment->amount * $profitRate;
                    $payment->save();
                    $processed++;
                }
            }
            $offset += $perPage;
        }

        $this->output->success("Processed {$processed} payments");
    }
}

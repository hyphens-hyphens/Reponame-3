<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\Payment;

class UpdatePaymentStatusCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:payment:update_status_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update new column `status_code` for table `payments`';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perPage = 5;
        $offset = 0;
        $processed = 0;
        $this->output->text("Updating payments status code");
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
            foreach ($payments as $payment) {
                $payment->status_code = Payment::getPaymentStatus($payment);
                $payment->save();
                $processed++;
            }
            $offset += $perPage;
        }

        $this->output->success("Processed {$processed} payments");
    }
}

<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Webklex\IMAP\ClientManager;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use Webklex\IMAP\Message;

class MoMoTransactionNotifierCommand extends Command
{
    /**
     * @var \T2G\Common\Services\DiscordWebHookClient
     */
    protected $discord;

    /**
     * @var \Webklex\IMAP\Client
     */
    protected $imap;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:momo:notifier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read mailbox to notify MoMo transaction';

    /**
     * Create a new command instance.
     *
     * @param \Webklex\IMAP\ClientManager $imap
     */
    public function __construct(ClientManager $imap)
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.payment_alert'));
        $this->imap = $imap->account(config('t2g_common.momo.mailbox', 'momo_mailbox'));
    }

    /**
     * Execute the console command.
     *
     * @throws \Webklex\IMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\IMAP\Exceptions\GetMessagesFailedException
     * @throws \Webklex\IMAP\Exceptions\InvalidWhereQueryCriteriaException
     * @throws \Webklex\IMAP\Exceptions\MessageSearchValidationException
     */
    public function handle()
    {
        try {
            $this->imap->connect();
            $this->output->title("Checking for new MoMo transactions - " . date('Y-m-d H:i:s'));
            $inbox = $this->imap->getFolder('INBOX');
        } catch (ConnectionFailedException $e) {
            $this->output->error("Cannot connect to mailbox. Reason: " . json_encode($this->imap->getErrors()));
            exit();
        }

        $yesterday = date( "d M Y", strtotime("-1 day"));
        $emails = $inbox->query()->whereText('Bạn vừa nhận được tiền')->whereSince($yesterday)->unseen()->get();
        if ($total = count($emails)) {
            $this->output->text("Going to process {$total} transactions");
        }
        /** @var \Webklex\IMAP\Message $email */
        $processed = 0;
        foreach ($emails as $email) {
            $this->output->text("Processing email {$email->getUid()}");
            file_put_contents($this->getStoragePath($email->getUid() . ".html"), $email->getHTMLBody());
            $this->markAsRead($email);
            $this->alertDiscord($email);
            $processed++;
        }
        $this->output->text("Processed {$processed} items");
        $this->output->success("Process checking MoMo transaction ended - " . date('Y-m-d H:i:s'));
        $this->imap->disconnect();
    }

    private function getStoragePath($path)
    {
        return storage_path("app/public/momo/{$path}");
    }

    public function getReviewUrl(Message $email)
    {
        return url("storage/momo/{$email->getUid()}.html");
    }

    private function alertDiscord(Message $email)
    {
        $message = $this->parseAlertMessage($email->getHTMLBody(), $this->getReviewUrl($email));
        if ($message) {
            $this->discord->send($message);
        }
    }

    private function markAsRead(Message $email)
    {
        $email->setFlag("\\Seen \\Flagged");
    }

    /**
     * @param $emailBody
     * @param $linkReview
     *
     * @return string
     */
    private function parseAlertMessage($emailBody, $linkReview)
    {
        $crawler = new Crawler($emailBody);
        $senderPhoneNode = $crawler->filterXPath("(//*[contains(text(),'Số điện thoại người gửi')])/../..//td[last()]/div");
        if (!$senderPhoneNode->text('')) {
            return "";
        }
        $amountNode = $crawler->filterXPath("(//*[contains(text(),'Số tiền')])/../..//td[last()]/div");
        $senderNode = $crawler->filterXPath("(//*[contains(text(),'Người gửi')])/../..//td[last()]/div");
        $noteNode = $crawler->filterXPath("(//*[contains(text(),'Tin nhắn')])/../..//td[last()]/div");
        if (!$noteNode->text('')) {
            $noteNode = $crawler->filterXPath("(//*[contains(text(),'Lời chúc')])/../..//td[last()]/div");
        }
        $timeNode = $crawler->filterXPath("(//*[contains(text(),'Thời gian')])/../..//td[last()]/div");

        $alert = sprintf(
            "[MoMo] Nhận được số tiền `%s` từ `%s` vào lúc %s.",
            trim($amountNode->text('')),
            trim($senderNode->text('')),
            trim($timeNode->text(''))
        );
        if ($note = trim($noteNode->text(''))) {
            $alert .= " Nội dung: `{$note}`.";
        }
        $alert .= " $linkReview";

        return $alert;
    }
}

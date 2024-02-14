<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailFrom;
use App\Models\Scrap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\ClientManager;

class ProcessScraping implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email_id;
    private $page;
    private $page_size;
        /**
     * Create a new job instance.
     */
    public function __construct($email_id, $page,$page_size)
    {
        $this->email_id = $email_id;
        $this->page = $page;
        $this->page_size = $page_size;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("thre");
        $email = Email::find($this->email_id);
        $offset = ($this->page - 1) * $this->page_size;
        $email_froms = EmailFrom::where('email_id', $this->email_id)
            ->offset($offset)
            ->limit($this->page_size)
            ->get();
        $cm = new ClientManager('config/imap.php');
        $client= $cm->make([
            'host'          => 'imap.gmail.com',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $email->email,
            'password'      => $email->app_password,
            'protocol'      => 'imap'
        ]);   
        $inboxFolder = $client->getFolderByName('INBOX');
        Log::info("thre-2");
        foreach($email_froms as $from){
            Log::info("thre-44");
            $values = [2,2,3, 4];
            $randomNumber = $values[rand(0, count($values) - 1)];
            $counts = $inboxFolder->search()->from($from->email_from)->all()->limit($limit = $randomNumber, $page = 1)->get();
            foreach($counts as $count){
                Log::info("thre-cc");
                $scrap = new Scrap();
                $scrap->email_id = $this->email_id;
                $scrap->from_email = $from->email_from;
                $scrap->header = $count->getHeader()->raw;
                $scrap->body = $count->getRawBody();
                $scrap->save();
            }
        }
    }
}

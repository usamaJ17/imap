<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Models\Email;
use App\Models\EmailFrom;
use App\Models\ParentDomain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;

class FetchFrom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $inboxFolder;
    private $part;
    private $acc_id;
    private $email;
    private $email_password;
    /**
     * Create a new job instance.
     */
    public function __construct($part,$acc_id,$email,$email_password)
    {
        $this->part = $part;
        $this->acc_id = $acc_id;
        $this->email = $email;
        $this->email_password = $email_password;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            $cm = new ClientManager('config/imap.php');
            $client= $cm->make([
                'host'          => 'imap.gmail.com',
                'port'          => 993,
                'encryption'    => 'ssl',
                'validate_cert' => true,
                'username'      => $this->email,
                'password'      => $this->email_password,
                'protocol'      => 'imap'
            ]);  
            $inboxFolder = $client->getFolderByName('INBOX');
            $emails = $inboxFolder->search()
                ->all()
                ->setFetchOrderDesc()
                ->setFetchBody(false)
                ->limit($limit = 100, $page = $this->part)
                ->get();
            $fromAddresses = [];
            $fet_count = 0;
            foreach ($emails as $email) {
                try{
                    if (preg_match('/^(.*?) <(.*?)>$/', $email->get("from")->toString(), $matches)) {
                        $domain = str_replace('"', "", trim($matches[1]));
                        $emailAddress = $matches[2];
                        $main_domain = explode('@', $matches[2])[1];
                    }
                    $em = EmailFrom::where('email_from',$emailAddress)->where('email_id',$this->acc_id)->first();
                    if($em){
                        $em->count = $em->count + 1;
                        $em->save();
                    }else{
                        $p_d =ParentDomain::where('parent_domain',$domain)->first();
                        if($p_d){
                            $p_d->count = $p_d->count + 1;
                            $p_d->save();
                        }else{
                            $p_d = new ParentDomain();
                            $p_d->parent_domain = $domain;
                            $p_d->count = 1;
                            $p_d->save();
                        }
                        $dom = Domain::where('domain',$main_domain)->where('parent_domain',$p_d->id)->first();
                        if($dom){
                            $dom->count = $dom->count + 1;
                            $dom->save();
                        }else{
                            $dom = new Domain();
                            $dom->domain = $main_domain;
                            $dom->parent_domain = $p_d->id;
                            $dom->count = 1;
                            $dom->save();
                        }
                        $email_from = new EmailFrom();
                        $email_from->domain_id = $dom->id;
                        $email_from->parent_domain_id = $p_d->id;
                        $email_from->email_from = $emailAddress;
                        $email_from->email_id = $this->acc_id;
                        $email_from->save();
                    }
                    $fet_count++;
                }catch (\Exception $exception) {
                    // Log the error and continue with the next iteration
                    Log::error('Error processing email in job: ' . $exception->getMessage());
                }
            }
            $email_conf = Email::find($this->acc_id);
            $email_conf->total_fetched = $email_conf->total_fetched + $fet_count;
            $email_conf->save();
        }catch (\Exception $exception) {
            Log::error('Error in job execution: ' . $exception->getMessage());
        }
    }
}

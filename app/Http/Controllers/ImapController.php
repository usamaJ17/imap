<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailFrom;
use App\Models\OrgEmailFrom;
use Illuminate\Http\Request;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\ClientManager;
use App\Exports\EmailExport;
use App\Jobs\FetchFrom;
use App\Models\Domain;
use App\Models\ParentDomain;
use Maatwebsite\Excel\Facades\Excel;


class ImapController extends Controller
{
    protected $email_domains = [
        "gmail.com",
        "yahoo.com",
        "outlook.com",
        "aol.com",
        "icloud.com",
        "yandex.com",
        "mail.ru",
        "protonmail.com",
        "zoho.com",
        "comcast.net",
        "verizon.net",
        "qq.com",
        "163.com",
        "126.com",
        "gmx.com",
        "rediff.com",
        "rocketmail.com",
        "aim.com",
        "att.net",
        "live.com",
        'hotmail.com',
        'hotmail.co.uk',
        'msn.com',
        'yahoo.fr',
        'wanadoo.com'
    ];
    
    public function imap(Request $request){
        $client = Client::account('default');
        $client->connect();

        // Get the "INBOX" folder (you can change the folder name if needed)
        $inboxFolder = $client->getFolderByName('INBOX');

        // Calculate the date 7 days ago
        $sevenDaysAgo = now()->subDays(10);
        $string = "";
        // Search for unread emails that are not older than 7 days
        $unreadEmails = $inboxFolder->search()
            ->from('ae-notice.ae3@mail.aliexpress.com')->get();
        $string = $unreadEmails[0]->getHeader()->raw.$unreadEmails[0]->getRawBody();
        $filePath = public_path('string.txt');

        // Append the string to the file
        if (file_put_contents($filePath, $string, FILE_APPEND) !== false) {
            return "String appended to the file successfully.";
        } 
        //dd($unreadEmails[0]->getHeader()->raw,$unreadEmails[0]->getRawBody());
    }
    public function GetImap(){
        $all_email_from = Email::all(); 
        return view('imap_form')->with(compact('all_email_from'));
    }
    public function showEmailForm(){
        $all_email_from = Email::all(); 
        return view('email_form')->with(compact('all_email_from'));
    }
    public function storeFrom(Request $request){
        $numberOfEmails = $request->input('number_of_emails');
        $email_conf = Email::where('id',$request->account_id)->first();    
        $cm = new ClientManager('config/imap.php');
        $client= $cm->make([
            'host'          => 'imap.gmail.com',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $email_conf->email,
            'password'      => $email_conf->app_password,
            'protocol'      => 'imap'
        ]);    
        // Get the "INBOX" folder (you can change the folder name if needed)
        $inboxFolder = $client->getFolderByName('INBOX');
        $count = $inboxFolder->search()->all()->count();
        $email_conf->total = $count;
        $email_conf->save();
        $parts = floor($count / 100) + 1;
        for ($i=1; $i <= $parts; $i++) { 
            FetchFrom::dispatch($i,$request->account_id,$email_conf->email,$email_conf->app_password);
        }
        return redirect()->back()->with('message', 'Emails Storing Started.');
    }
    public function setOrganizationalEmail($id){
        $emails = Email::all();
        foreach($emails as $email){
            $all = EmailFrom::where('email_id',$email->id)->get();
            foreach($all as $item){
                $email_dom = explode('@',$item->email_from);
                $email_dom = $email_dom[1];
                // check if email domain is in the list of email domains
                if(! in_array($email_dom,$this->email_domains)){
                    $item->is_organizational_email = 1;
                    $item->save();
                }
            }
        }
        echo "DONE";
    }
    public function getOrganizationalEmails($id){
        $all = EmailFrom::where('is_organizational_email',1)->where('email_id',$id)->get();
        $email_conf = Email::where('id',$id)->first();    
        $cm = new ClientManager('config/imap.php');
        $client= $cm->make([
            'host'          => 'imap.gmail.com',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $email_conf->email,
            'password'      => $email_conf->app_password,
            'protocol'      => 'imap'
        ]);        
        // Get the "INBOX" folder (you can change the folder name if needed)
        $inboxFolder = $client->getFolderByName('INBOX');
        foreach($all as $item){
            $email = $inboxFolder->search()->from($item->email_from)->get();
            foreach($email as $e){
                $org = new OrgEmailFrom();
                $org->email_from_id = $id;
                $org->header = json_encode($e->getHeader()->raw);
                $org->body = json_encode($e->getRawBody());
                $org->save();
            }
        }
        echo "DONE";
    }

    public function getExcel($id = null){
        return Excel::download(new EmailExport($id) ,'email.xlsx');
    }
}

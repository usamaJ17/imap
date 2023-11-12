<?php

namespace App\Exports;

use App\Models\EmailFrom;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmailExport implements FromCollection
{
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function collection()
    {
        // Return the specific id you want to export
        if($this->id){
            return EmailFrom::where('email_from_id',$this->id)->where('is_organizational_email',1)->get();
        }else{
            return EmailFrom::where('is_organizational_email',1)->get();
        }        
    }
}

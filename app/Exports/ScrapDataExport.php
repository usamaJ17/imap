<?php

namespace App\Exports;

use App\Models\Scrap;
use App\Models\Email;
use App\Models\EmailFrom;
use App\Models\Domain;
use App\Models\ParentDomain;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ScrapDataExport implements FromCollection, WithHeadings , ShouldAutoSize
{
    protected $page;
    protected $page_size;

    public function __construct($page, $page_size)
    {
        $this->page = $page;
        $this->page_size = $page_size;
    }

    public function collection()
    {
        $offset = ($this->page - 1) * $this->page_size;

        return Scrap::offset($offset)
            ->limit($this->page_size)
            ->get()
            ->map(function ($data) {
                $email = Email::find($data->email_id);
                $email_from = EmailFrom::where('email_id', $data->email_id)->where('email_from', $data->from_email)->first();
                $d_name = "";
                $p_name = "";
                if ($email_from) {
                    $d = Domain::find($email_from->domain_id);
                    if ($d) {
                        $d_name = $d->domain;
                    }
                    $p = ParentDomain::find($email_from->parent_domain_id);
                    if ($p) {
                        $p_name = $p->parent_domain;
                    }
                }
                return [
                    'To' => optional($email)->email,
                    'From' => $data->from_email,
                    'Domain' => $d_name,
                    'Parent Domain' => $p_name,
                    'Header' => $data->header,
                    'Body' => $data->body,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'To',
            'From',
            'Domain',
            'Parent Domain',
            'Header',
            'Body',
        ];
    }
}

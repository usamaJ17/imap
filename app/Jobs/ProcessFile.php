<?php

namespace App\Jobs;

use App\Exports\ScrapDataExport;
use App\Models\Domain;
use App\Models\Email;
use App\Models\EmailFrom;
use App\Models\ParentDomain;
use App\Models\Scrap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $page;
    private $page_size;
        /**
     * Create a new job instance.
     */
    public function __construct($page,$page_size)
    {
        $this->page = $page;
        $this->page_size = $page_size;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $page = $this->page;
        $page_size = $this->page_size;
        $fileName = $page . '_' . Str::random(5) . '.xlsx';
        $filePath = 'exports/' . $fileName; // Define your custom folder here
        Excel::store(new ScrapDataExport($page, $page_size), $filePath);
    }
}

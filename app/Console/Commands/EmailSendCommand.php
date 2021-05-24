<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\CreateUserMessageTrait;

class EmailSendCommand extends Command
{
    use CreateUserMessageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:notificationUpdateSend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sending emails to users, to inform them of changes to their custom set notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->sendNotifications();

        return 0;
    }
}

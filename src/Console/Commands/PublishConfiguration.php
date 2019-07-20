<?php
/**
 * PublishConfiguration.php
 *
 * This command is to publish all neccessary configuration files for modifying the package's behavior
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */
namespace Bkstar123\ApiBuddy\Console\Commands;

use Illuminate\Console\Command;
use Bkstar123\ApiBuddy\ApiBuddyServiceProvider;
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;

class PublishConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apibuddy:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all neccessary configuration files for modifying ApiBuddy package\'s behavior' ;

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
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--provider' => ApiBuddyServiceProvider::class]);
        $this->call('vendor:publish', ['--provider' => CorsServiceProvider::class]);
    }
}

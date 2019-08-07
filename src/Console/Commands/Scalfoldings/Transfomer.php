<?php
/**
 * Transformer Scalfolding Command
 *
 * This command is to scalfold a transformer class
 *
 * @author: tuanha
 * @last-mod: 07-Aug-2019
 */
namespace Bkstar123\ApiBuddy\Console\Commands\Scalfoldings;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Transformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apibuddy:makeTransformer {transformer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scalfold a transformer class for API response transforming' ;

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
        $transformer = $this->argument('transformer');
        $data = require(__DIR__.'/Templates/transformer.php');
        $filePath = app_path().'/Transformers';
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $file = $filePath.'/'.$transformer.'.php';
        if (!file_exists($file)) {
            file_put_contents($file, $data);
            $this->info($file. ' has been successfully created');
        }
        $this->info($file. ' already exists');
    }
}

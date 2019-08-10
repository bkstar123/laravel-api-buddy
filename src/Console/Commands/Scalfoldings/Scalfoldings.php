<?php
/**
 * Scalfoldings Command
 *
 * This command is to scalfold a transformer, resource or an API controller
 *
 * @author: tuanha
 * @last-mod: 08-Aug-2019
 */
namespace Bkstar123\ApiBuddy\Console\Commands\Scalfoldings;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Scalfoldings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apibuddy:make {--t|type=} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scalfold a transformer, resource or an API controller' ;

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
        $type = $this->option('type');
        $name = $this->argument('name');
        $allowedTypes = ['transformer', 'resource', 'controller'];
        if (empty($type)) {
            return $this->error("The option --type must be given");
        }
        switch ($type) {
            case 'transformer':
                $filePath = app_path('Transformers');
                break;
            case 'resource':
                $filePath = app_path('Http/Resources');
                break;
            case 'controller':
                $filePath = app_path('Http/Controllers');
                break;
            default:
                return $this->error("The option --type must be one of the following values (case-sensitive):\n".
                "-resource, controller, transformer");
                break;
        }
        $data = require(__DIR__."/Templates/$type.php");
        if (!file_exists($filePath)) {
            if (mkdir($filePath, 0755, true) === false) {
                return $this->info('Error creating one of the file path elements, please verify permissions');
            }
        }
        $file = "$filePath/$name.php";
        if (!file_exists($file)) {
            if (file_put_contents($file, $data) === false) {
                return $this->info('Error writing content to the file, please verify permissions');
            }
            return $this->info($file. ' has been successfully created');
        } else {
            return $this->info($file. ' already exists');
        }
    }
}

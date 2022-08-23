<?php

namespace Neko\LaravelStapler\Commands;

use Illuminate\Console\Command;
use Neko\LaravelStapler\Exceptions\InvalidClassException;
use Neko\LaravelStapler\Services\ImageRefreshService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'stapler:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate images for a given model (and optional attachment and styles)';

    /**
     * Create a new command instance.
     *
     * @param  ImageRefreshService  $imageRefreshService  The image refresh service that will be used to rebuild images.
     */
    public function __construct(protected ImageRefreshService $imageRefreshService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->imageRefreshService->setOutput($this->output);

        $class       = $this->argument('class');
        $attachments = $this->option('attachments') ?: '';

        $this->info('Refreshing uploaded images...');
        try {
            $this->imageRefreshService->refresh($class, $attachments);
        } catch (InvalidClassException $e) {
            $this->error($e->getMessage());
        } finally {
            $this->info('Done!');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['class', InputArgument::REQUIRED, 'The name of a class (model) to refresh images on'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['attachments', null, InputOption::VALUE_OPTIONAL, 'A list of specific attachments to refresh images on.'],
        ];
    }
}

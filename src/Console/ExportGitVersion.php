<?php

namespace Msleonar\Gitexport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ExportGitVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:export
        {commit? : Optional, the commit of the git hash}
        {--N|no-cache : Explicitly prohibit rebuilding the cache before and after running reading the current git hash }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the git commit to your .env file';

    /**
     * Create a new command instance.
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
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        // Be absolutely sure about whether or not the info is available
        if (!$this->option('no-cache')) {
            $this->callSilent('config:clear');
        }

        $oldCommit = config('git.hash');
        $oldTime = config('git.date');

        $this->comment('Old Hash: '.$oldCommit);
        $this->comment('Old Date: '.$oldTime);

        if (!empty($this->argument('commit'))) {
            $newCommit = trim($this->argument('commit'));
        } else {
            $process = new Process('git rev-parse HEAD');
            $process->run();

            // Error checking
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $newCommit = trim($process->getOutput());
        }

        $process = new Process('git show -s --format=%ct '.$newCommit);
        $process->run();

        // Error checking
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $newTime = trim($process->getOutput());

        if (!empty($oldCommit)) {
            $str = str_replace("COMMIT_HASH={$oldCommit}", "COMMIT_HASH={$newCommit}", $str);
        } else {
            $str .= "\nCOMMIT_HASH={$newCommit}\n";
        }

        if (!empty($oldTime)) {
            $str = str_replace("COMMIT_DATE={$oldTime}", "COMMIT_DATE={$newTime}", $str);
        } else {
            $str .= "\nCOMMIT_DATE={$newTime}\n";
        }

        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);

        // Cache the values for the user
        if (!$this->option('no-cache')) {
            $this->callSilent('config:cache');
        }

        $this->info('New Hash: '.$newCommit);
        $this->info('New Date: '.$newTime);
    }
}

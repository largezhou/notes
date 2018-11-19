<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化系统';

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
        do {
            $pw = $this->secret('设置密码 [000000]：') ?: '000000';
            $pwConfirmation = $this->secret('再次输入密码 [000000]：') ?: '000000';

            if ($pw !== $pwConfirmation) {
                $this->error('两次密码不一致');
            }
        } while ($pw !== $pwConfirmation);

        $this->info($pw);

        $user = User::updateOrCreate(['username' => 'largezhou'], ['password' => bcrypt($pw)]);

        if ($user->wasRecentlyCreated) {
            $this->info('largezhou 创建成功');
        } else {
            $this->info('密码修改成功');
        }
    }
}

<?php

/**
 * return [
    'blog' => [
        'root' => '/home/wwwroot/blog',
        'git_path' => '',
        'email' => '',
        'name' => '',
        'git_bash_path' => '',
        'password' => '',
        'branch' => '',
        'log_name' => ''
    ]
   ];
 */
$configs = require __DIR__ . '/config.php';

if (empty($configs)) {
    echo '配置为空.';
    exit;
}

foreach ($configs as $config) {
    if (!isset($config['root'], $config['git_path'], $config['email'], $config['name'], $config['git_bash_path'], $config['password'], $config['branch'], $config['log_name'], $config['additional_cmd'])) {
        continue;
    }
    $additionalCmd = $config['additional_cmd']; //其他需要执行的命令
    $savePath = $config['root']; //"网站根目录"
    $gitPath  = $config['git_path']; //代码仓库 一定要使用ssh方式不然每次都得输入密码
    $email = $config['email']; //"用户仓库邮箱";
    $name  = $config['name']; //"仓库用户名,一般和邮箱一致即可";
    $git = $config['git_bash_path']; //"GIT全局路径";
    $password = $config['password']; //"在GITEE设置的密码";
    $branch = $config['branch']; //"你需要pull的分支";
    $logName = $config['log_name']; //"LOG名称";
    $logPath = './logs';
    if (!file_exists($logPath)) {
        mkdir($logPath);
    }
    $logName = $logPath . '/' . $logName;
    file_put_contents("$logName.log", PHP_EOL.date('Y-m-d H:i:s', time()).": ".PHP_EOL, FILE_APPEND);
    $requestBody = file_get_contents("php://input");
    if (empty($requestBody)) {
        file_put_contents("$logName.log", "FAILED".PHP_EOL, FILE_APPEND);
        die('send fail');
    }
    file_put_contents("$logName.log", $requestBody, FILE_APPEND);
    //json格式
    $content = json_decode($requestBody, true);
    //验证密码
    if($content['password'] == $password){
	echo json_encode(['ok' => true]);
        //指定分支，有提交时
        if ($content['ref'] == "refs/heads/$branch" && $content['total_commits_count'] > 0) {
            $result = shell_exec("cd $savePath && $git clean -f && $git pull origin $branch 2>&1");
            $res_log = "[ PULL START ]".PHP_EOL;
            $res_log .= $result;
            $res_log .= PHP_EOL . PHP_EOL;
            file_put_contents("$logName.log", $res_log, FILE_APPEND);
            //执行额外的命令,如数据库迁移等
            if ($config['additional_cmd']) {
                $additionalBashResult = shell_exec($additionalCmd);
                $cmdLog = '执行额外命令开始' . PHP_EOL;
                $cmdLog .= $additionalBashResult;
                $cmdLog .= PHP_EOL;
                file_put_contents("$logName.log", $cmdLog, FILE_APPEND);
            }
        }
    } else {
        file_put_contents("$logName.log", 'Password is Incorrect!', FILE_APPEND);
        echo 'Password is Incorrect!';
    }
}

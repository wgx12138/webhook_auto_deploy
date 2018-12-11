<?php

include_once './config.php';

if (!isset($configs)) {
    echo '无配置文件';
    exit;
}
if (empty($configs)) {
    echo '配置为空.';
    exit;
}

foreach ($configs as $config) {
    if (!isset($config['root'], $config['git_path'], $config['email'], $config['name'], $config['git_bash_path'], $config['password'], $config['branch'], $config['log_name'])) {
        continue;
    }
    $savePath = $config['root']; //"网站根目录"
    $gitPath  = $config['git_path']; //代码仓库 一定要使用ssh方式不然每次都得输入密码
    $email = $config['email']; //"用户仓库邮箱";
    $name  = $config['name']; //"仓库用户名";
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
    $content = json_decode($requestBody, true);
    if($content['password'] == $password){
        if ($content['ref']=="refs/heads/$branch") {
            $result = shell_exec("cd $savePath && $git clean -f && $git pull origin $branch 2>&1");
            $res_log = "[ PULL START ]".PHP_EOL;
            $res_log .= $result;
            $res_log .= PHP_EOL . PHP_EOL;
            file_put_contents("$logName.log", $res_log, FILE_APPEND);
            echo $result;
        }
    } else {
        file_put_contents("$logName.log", 'Password is Incorrect!', FILE_APPEND);
        echo 'Password is Incorrect!';
    }
}

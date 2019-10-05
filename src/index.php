<?php
require '../vendor/autoload.php';

use VK\Client\VKApiClient;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;

$access_token = "acf88ccd53b23c904670b75488912b1c1e74616a3bffe2ae49a872b6f80a65f3a249da59c52b82b965d30";

if (2 != count($argv)){
    exit("Неврное количество аргументов\n");
}

$user_id = $argv[1];

if (!ctype_digit($user_id)){
    exit(sprintf("Неверное значение аргумента: %s\n", $user_id));
}

$vk = new VKApiClient();

try {
    $result = $vk->getRequest()->post('execute', $access_token, [
            'code' => '
                var friends = API.friends.get({"user_id": ' . $user_id . '}).items;
                var followers = API.users.getFollowers({"user_id": ' . $user_id . '}).items;
                return friends + followers;
            '
        ]
    );

}catch (VKClientException | VKApiException $e) {
    exit($e->getMessage().PHP_EOL);
}

if (null == $result){
    exit("Получен нулевой ответ от сервера");
}

try{
    $fileName = $user_id."_friends_and_followers";
    $file = fopen('../resource/'.$fileName, 'w+');

    if (!$file){
        throw new Exception("Не удалось открыть файл для записи");
    }

    foreach ($result as $row){
        fwrite($file, $row.PHP_EOL);
    }
    fclose($file);

}catch (Exception $e){
    exit($e->getMessage().PHP_EOL);
}

$format = "Друзья и подписчики пользователя %s успешно записаны в файл %s\n";
print(sprintf($format, $user_id, $fileName));








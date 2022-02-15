<?php

    const TOKEN = '5248782993:AAGvd53d0Z7vs36pdTmVsY_hCX3UUJJp6Zk';
    const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';
    const help_list = ["/help - Показать список команд", "/расписание - Посмотреть расписание группы", "/новости - Показать новости", '/кнопки - Показать кнопки'];
    const faculties = ['rtf' => 'Радиотехнический факультет', 'rkf' => 'Радиоконструкторский факультет', 'fvs' => 'Факультет вычислительных систем',
                       'fsu' => 'Факультет систем управления', 'fet' => 'Факультет электронной техники', 'fit' => 'Факультет инновационных технологий', 'ef' => 'Экономический факультет',
                       'gf' => 'Гуманитарный факультет', 'yuf' => 'Юридический факультет', 'fb' => 'Факультет безопасности',
                        'zivf' => 'Заочный и вечерний факультет', 'aspirantura' => 'Аспирантура'];
    require_once 'tusur-timetable.php';
    
    $update = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY);
	$chat_id = $update['message']['chat']['id'];
	$msg = strtolower($update['message']['text']);
	$keyboard = [
        'keyboard' => 
        [
            [
                ['text' => 'Показать список команд'],
            ],
            [
                ['text' => 'Посмотреть расписание группы'],
            ],
            [
                ['text' => 'Показать новости'],
            ],
        ]
    ];
    $encodedKeyboard = json_encode($keyboard);

    if($msg)
	switch($msg) {
        case "/start":
            $first_name = $update['message']['from']['first_name'];
            $welcome_text = "Привет, " . $first_name . "!\n";
            $method = 'sendMessage';
            $data = [
                'text' => $welcome_text . showHelpList(),
            ];
            break;
        
        case "/help":
        case "Показать список команд":
            $method = 'sendMessage';
            $data = [
                'text' => showHelpList(),
            ];
            break;

        case "/расписание":
        case "Посмотреть расписание группы":
            $method = 'sendMessage';
            $data = [
                'text' => "Пожалуйста, отправь сообщением название факультета и номер группы в следующем формате:
                \r\n<факультет> <номер группы>\n\nДоступны следующие факультеты:\n\n" . showFaculties(),
            ];
            break;

        case "/новости":
        case "Показать новости":
            break;

        case "/кнопки":
            $method = 'sendMessage';
            $data = [
                'reply_markup' => $encodedKeyboard,   
            ];
            break;

        default:
            $method = 'sendMessage';
            $data = [
            'text' => 'Извините, не понимаю о чем вы.',
            ];
        }

    $data['chat_id'] = $chat_id;
    sendMethod($method, $data);
	

    // Helpers


    function showHelpList() 
    {
        $help = "Вот список моих комманд:\n";
        for($i = 0; $i < count(help_list); $i++) {
            $help .= help_list[$i] . "\n";
        }
        return $help;
    }
	
    
    function sendMethod($method, $params = []) 
    {
        $url = BASE_URL . $method;
        $curld = curl_init();
        curl_setopt($curld, CURLOPT_POST, true);
        curl_setopt($curld, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curld, CURLOPT_URL, $url);
        curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curld);
        curl_close($curld);
        return $output;

    }

    function showFaculties() 
    {
        $output = '';
        foreach(faculties as $name => $description) {
            $output .= $name . ' - ' . $description . "\n";
        }
        return $output;
    }

?>

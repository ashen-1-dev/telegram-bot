<?php

    const TOKEN = '5248782993:AAGvd53d0Z7vs36pdTmVsY_hCX3UUJJp6Zk';
    const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';
    const help_list = ["/help - Показать список команд", "/расписание - Посмотреть расписание группы", "/новости - Показать новости", '/кнопки - Показать кнопки'];
    const faculties = ['rtf' => 'Радиотехнический факультет', 'rkf' => 'Радиоконструкторский факультет', 'fvs' => 'Факультет вычислительных систем',
                       'fsu' => 'Факультет систем управления', 'fet' => 'Факультет электронной техники', 'fit' => 'Факультет инновационных технологий', 'ef' => 'Экономический факультет',
                       'gf' => 'Гуманитарный факультет', 'yuf' => 'Юридический факультет', 'fb' => 'Факультет безопасности',
                        'zivf' => 'Заочный и вечерний факультет', 'aspirantura' => 'Аспирантура'];
    const week_days = ['пн' => 1, 'вт' => 2, 'ср' => 3, 'чт' => 4, 'пт' => 5, 'сб' => 6];
    require_once 'tusur-timetable.php';
    require_once 'tusur-news.php';
    $stream = file_get_contents('php://input');
    $update = json_decode($stream, JSON_OBJECT_AS_ARRAY);
    file_put_contents('telegram-logs.txt', print_r($update, 1), FILE_APPEND);
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
    $commandAndArguments = parseCommand($msg);

    if($commandAndArguments[0] == 'расписание' && count($commandAndArguments) === 4) {
        $timetable = getWeekTimetable($commandAndArguments[1], $commandAndArguments[2]);
        $method = 'sendMessage';
        $data = [
            'text' => showTimetable($timetable, $commandAndArguments[3]),
            'chat_id' => $chat_id,
        ];
        sendMethod($method, $data);
        exit;
    }

    

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
                \r\n/расписание <факультет> <номер группы> <день недели>\n\nНапример: /расписание fvs 599-1 пн
                \r\nДоступны следующие факультеты:\n\n" . showFaculties(),
            ];
            break;

        case "/новости":
        case "Показать новости":
            $method = 'sendMessage';
            $data = [
                'text' => getLatestNews(),
                'disable_web_page_preview' => false,
                'parse_mode' => 'HTML',
            ];
            break;

        case "/кнопки":
            $method = 'sendMessage';
            $data = [
                'text' => 'Лови',
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

    function parseCommand($msg) {
        if(strpos($msg, '/') !== 0)
            return false;
        $commandAndArguments = explode(' ', str_replace('/', '', $msg));

        return $commandAndArguments;
    }

    function debug($debug, $chat_id) 
    {
        sendMethod('sendMessage', ['chat_id' => $chat_id, 'text' => count($debug)]);
        die();
    }

    function showTimetable($timetable, $day) 
    {
        $day_id = week_days[$day];
        $output =  hex2bin('e29d97') . 'Твое расписание на ' . $timetable[$day_id]['date'] . "\n\n";
        
        if(count($timetable[$day_id]) <= 1)
                return 'Можешь расслабиться, на этот день пар нет.';

        for($lesson = 1; $lesson < 8; $lesson++) {
            $curr_lesson = $timetable[$day_id][$lesson];

            if(!empty($curr_lesson)) {
                $output .=  hex2bin('e29e96') . $curr_lesson['discipline'] . "\n" . 
                            $curr_lesson['kind'] . "\n"; 

                if($curr_lesson['remote'] != null)
                    $output .= mb_strtolower($curr_lesson['remote'] . "\n\n", 'UTF-8'); 
                else 
                    $output .= $curr_lesson['auditoriums'] . "\n\n";
            }                           
        }
        return $output;
    }
?>

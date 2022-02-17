<?php
    const url = "https://timetable.tusur.ru/";
    const lesson_attributes = [1 => 'discipline', 2 => 'kind', 3 => 'auditoriums', 4 => 'group', 5 => 'remote'];
    
    function getWeekTimetable($faculties, $group) {
        $timetable = [];
        $dom = new DOMDocument();
        $current_url = url . "faculties/" . $faculties . "/groups/" . $group;
        if(!$dom->loadHTMLFile($current_url))
            return null;
        $xpath = new DomXPath($dom);

        for($day = 1; $day < 7; $day++) {
            $date_query = "//div[@class='timetable_wrapper']//table//thead//tr//th";
            $date_node = $xpath->query($date_query);
            $timetable[$day]['date'] = str_replace(array("\n", '   '), '', $date_node[$day]->nodeValue);
    
            for($lesson = 1; $lesson < 8; $lesson++) {
                $lesson_query = "//table[2]//tr[@class='lesson_{$lesson}']//td[contains(@class, 'day_{$day}')]//div[@class='hidden for_print']//span";
                $lesson_nodes = $xpath->query($lesson_query);    
                
                $i = 1; 
                foreach($lesson_nodes as $lesson_node) {
                    $timetable[$day][$lesson][lesson_attributes[$i]] = $lesson_node->nodeValue;
                    $i++;
                }
            }
            
        }
        return $timetable;
    }

?>
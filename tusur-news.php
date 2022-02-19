<?php
    const main_url = 'https://tusur.ru/';


    function getLatestNews() {
        $dom = new DOMDocument();
        if(!$dom->loadHTMLFile(main_url))
            return null;
        
        $xpath = new DOMXPath($dom);
        $news_image_node_list = $xpath->query("//div[contains(@class, 'item news')]//div[contains(@class, 'image')]//a//img");
        $news_title_node_list = $xpath->query("//div[contains(@class, 'item news')]//div[contains(@class, 'title')]//a");
        $news_url =   main_url . $news_title_node_list[0]->getAttribute('href');
        $image_url =  $news_image_node_list[0]->getAttribute('src');
        $text = '<a href="' . $image_url  .'">&#8205;</a>';
        $text = $news_title_node_list[0]->nodeValue;
        $text .= "\n\n\r Подробнее на " . $news_url;
        

        return $text;
        
    }
?>


        
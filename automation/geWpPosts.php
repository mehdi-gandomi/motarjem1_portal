<?php

require("../App/Dependencies/CurlRequest.php");
require("../Core/Model.php");

function wpPosts()
{
    
        $curl = new CurlRequest("http://www.motarjem1.com/blog/wp-json/wp/v2/posts?per_page=3&page=1");
        $posts = [];
        $postData = $curl->execute_and_parse_json(true);
        $monthNames = ["", 'فروردین', "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"];
        foreach ($postData as $post) {
            $data = array(
                'title' => $post["title"]["rendered"],
                "previewText" => $post["excerpt"]["rendered"],
                "link" => $post["guid"]["rendered"],
            );
            $date = new DateTime($post["date"]);
            $dateParts = explode("-", $date->format("Y-m-d"));
            $persian = gregorian_to_jalali($dateParts[0], $dateParts[1], $dateParts[2]);
    
            $data["date"] = $persian[2] . " " . $monthNames[$persian[1]] . " " . $persian[0];
            $curl->set_url($post["_links"]['wp:featuredmedia'][0]['href']);
            $postDetails = $curl->execute_and_parse_json(true);
            if (isset($postDetails["media_details"])) {
                $data["thumbnail"] = $postDetails["media_details"]["sizes"]["medium_thumb"]["source_url"];
            } else {
                $curl->set_url($post["_links"]['wp:attachment'][0]['href']);
                $postDetails = $curl->execute_and_parse_json(true);
    
                $data["thumbnail"] = $postDetails[0]["guid"]["rendered"];
            }
    
            $curl->set_url($post["_links"]["wp:term"][0]["href"]);
            $postCategories = $curl->execute_and_parse_json(true);
            $links = [];
            foreach ($postCategories as $category) {
                array_push($links, array(
                    'link' => $category["link"],
                    'name' => $category["name"],
                ));
            }
            $data["categories"] = $links;
    
            $posts[] = $data;
        } 
        return $posts;
    
};

wpPosts();
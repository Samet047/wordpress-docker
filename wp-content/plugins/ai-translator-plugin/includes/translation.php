<?php

function call_deepl_translation_service($content, $api_key, $target_lang) {
    $url = 'https://api-free.deepl.com/v2/translate'; // Ücretsiz plan için endpoint
    // $url = 'https://api.deepl.com/v2/translate'; // Ücretli plan için endpoint

    $data = [
        'auth_key' => $api_key,
        'text' => $content,
        'source_lang' => 'EN',
        'target_lang' => strtoupper($target_lang), // Örn: 'TR', 'DE'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return new WP_Error('deepl_api_failed', 'DeepL API request failed: ' . curl_error($ch));
    }
    curl_close($ch);

    $response = json_decode($result, true);
    if (isset($response['message'])) {
        return new WP_Error('deepl_api_error', 'DeepL API error: ' . esc_html($response['message']));
    }

    return $response['translations'][0]['text'] ?? new WP_Error('deepl_translation_failed', 'DeepL translation failed.');
}

function translate_and_create_post($url, $target_lang = 'TR') {
    $google_api_key = get_option('ai_google_api_key');
    $deepl_api_key = get_option('ai_deepl_api_key');
    $preferred_api = get_option('ai_preferred_api', 'deepl');

    if (($preferred_api === 'deepl' && empty($deepl_api_key)) || ($preferred_api === 'google' && empty($google_api_key))) {
        return new WP_Error('missing_api_key', 'Preferred API key is not set.');
    }

    // Makale başlığını ve içeriğini önce al
    $article_title = fetch_article_title($url);
    if (is_wp_error($article_title)) {
        return $article_title;
    }

    $article_content = fetch_article_content($url);
    if (is_wp_error($article_content)) {
        return $article_content;
    }

    // Başlığı çevir
    if ($preferred_api === 'deepl') {
        $translated_title = call_deepl_translation_service($article_title, $deepl_api_key, $target_lang);
        if (is_wp_error($translated_title) && !empty($google_api_key)) {
            $translated_title = call_ai_translation_service($article_title, $google_api_key, $target_lang);
        }
    } else {
        $translated_title = call_ai_translation_service($article_title, $google_api_key, $target_lang);
        if (is_wp_error($translated_title) && !empty($deepl_api_key)) {
            $translated_title = call_deepl_translation_service($article_title, $deepl_api_key, $target_lang);
        }
    }

    if (is_wp_error($translated_title)) {
        return $translated_title;
    }

    // İçeriği çevir
    if ($preferred_api === 'deepl') {
        $translated_content = call_deepl_translation_service($article_content, $deepl_api_key, $target_lang);
        if (is_wp_error($translated_content) && !empty($google_api_key)) {
            $translated_content = call_ai_translation_service($article_content, $google_api_key, $target_lang);
        }
    } else {
        $translated_content = call_ai_translation_service($article_content, $google_api_key, $target_lang);
        if (is_wp_error($translated_content) && !empty($deepl_api_key)) {
            $translated_content = call_deepl_translation_service($article_content, $deepl_api_key, $target_lang);
        }
    }

    if (is_wp_error($translated_content)) {
        return $translated_content;
    }

    // Yeni yazı oluştur
    $post_id = wp_insert_post([
        'post_title'    => wp_strip_all_tags($translated_title),
        'post_content'  => wpautop($translated_content),
        'post_status'   => 'pending',
        'post_author'   => get_current_user_id(),
    ]);

    return $post_id;
}


function fetch_article_title($url) {
    $html = @file_get_contents($url);
    if ($html === FALSE) {
        return new WP_Error('fetch_title_failed', 'Başlık alınamadı. URL: ' . esc_html($url));
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $titleNodes = $dom->getElementsByTagName('title');
    if ($titleNodes->length > 0) {
        return trim($titleNodes->item(0)->nodeValue);
    } else {
        return new WP_Error('title_not_found', 'Makale başlığı bulunamadı.');
    }
}

function filter_and_clean_content($content) {
    // Süslü parantez ve normal parantez içindeki her şeyi kaldırmak için regex
    $content = preg_replace('/\{[^}]*\}/s', '', $content); // Süslü parantezler
    $content = preg_replace('/\([^)]*\)/s', '', $content); // Normal parantezler

    // Burada ekstra filtreler ekleyebilirsiniz (örn: HTML etiketleri, özel karakterler vs.)
    $content = strip_tags($content); // HTML etiketlerini kaldırır

    // Diğer gereksiz boşlukları temizler
    $content = trim($content);

    return $content;
}


function fetch_article_content($url) {
    $html = @file_get_contents($url);
    if ($html === FALSE) {
        return new WP_Error('fetch_content_failed', 'İçerik alınamadı.');
    }

    // Basit içerik alma (geliştirilebilir)
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $paragraphs = $xpath->query('//p');

    $content = '';
    foreach ($paragraphs as $p) {
        $content .= $p->textContent . "\n\n";
    }

    if (trim($content) === '') {
        return new WP_Error('empty_content', 'İçerik bulunamadı.');
    }

    return trim($content);
}



function call_ai_translation_service($content, $api_key, $target_lang) {
    $url = 'https://translation.googleapis.com/language/translate/v2';

    $data = [
        'q' => $content,
        'source' => 'en',
        'target' => strtolower($target_lang), // genellikle küçük harf ister
        'format' => 'text',
        'key' => $api_key,
    ];


    $response = wp_remote_post($url, [
        'body' => $data,
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('google_api_failed', 'Google Translate API hatası: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['error'])) {
        return new WP_Error('google_api_error', 'Google Translate API error: ' . $body['error']['message']);
    }

    return $body['data']['translations'][0]['translatedText'] ?? new WP_Error('google_translation_failed', 'Çeviri başarısız.');
}

?>
<?php
// Kısa kod: translate_article_form
add_shortcode('translate_article_form', 'translate_article_form_shortcode');

function translate_article_form_shortcode() {
    ob_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_url'])) {
        $url = esc_url_raw($_POST['article_url']);
        $target_lang = sanitize_text_field($_POST['target_lang']);

        if (function_exists('translate_and_create_post')) {
            $result = translate_and_create_post($url, $target_lang);

            if (is_wp_error($result)) {
                echo '<div class="ai-message ai-error">❌ Hata: ' . esc_html($result->get_error_message()) . '</div>';
            } else {
                echo '<div class="ai-message ai-success">✅ Çeviri başarılı! Yazı onaya gönderildi. (ID: ' . intval($result) . ')</div>';
            }
        } else {
            echo '<div class="ai-message ai-error">translate_and_create_post fonksiyonu tanımlı değil!</div>';
        }
    }
    ?>
    <style>
        .ai-translator-container {
            max-width: 600px;
            margin: 40px auto;
            background: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            font-family: "Segoe UI", sans-serif;
        }

        .ai-translator-container h2 {
            margin-bottom: 25px;
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        .ai-translator-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        .ai-translator-container input,
        .ai-translator-container select {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            color: #222;
            transition: border-color 0.2s;
        }

        .ai-translator-container input:focus,
        .ai-translator-container select:focus {
            border-color: #0073aa;
            outline: none;
        }

        .ai-translator-container button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .ai-translator-container button:hover {
            background-color: #005f8d;
        }

    </style>

    <div class="ai-translator-container">
        <h2><span>🌍</span> Makale Çevirisi</h2>
        <form method="post">
            <label for="article_url">🔗 Makale URL'si</label>
            <input type="url" name="article_url" id="article_url" placeholder="https://ornek.com/makale" required>

            <label for="target_lang">🌐 Hedef Dil</label>
            <select name="target_lang" id="target_lang" required>
                <option value="">Dil Seçin...</option>
                <option value="TR">🇹🇷 Türkçe</option>
                <option value="KU">🇹🇷🇮🇶 Kürtçe (Kurmancî)</option>
                <option value="EN">🇬🇧 İngilizce</option>
                <option value="DE">🇩🇪 Almanca</option>
                <option value="FR">🇫🇷 Fransızca</option>
                <option value="ES">🇪🇸 İspanyolca</option>
                <option value="IT">🇮🇹 İtalyanca</option>
                <option value="RU">🇷🇺 Rusça</option>
                <option value="JA">🇯🇵 Japonca</option>
                <option value="ZH">🇨🇳 Çince</option>
            </select>

            <button type="submit">🚀 Çevir ve Yazıyı Oluştur</button>
        </form>
    </div>


    <?php
    return ob_get_clean();
}
?>

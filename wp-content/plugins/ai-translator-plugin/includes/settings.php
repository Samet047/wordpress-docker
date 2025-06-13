<?php
function ai_translator_plugin_options() {
    ?>
    <div class="wrap">
        <h1>AI Translator Ayarları</h1>
        <p>Bu sayfadan çeviri işlemleri için kullanılacak API ayarlarını yapılandırabilirsiniz.</p>
        <div class="notice notice-info">
            <p><strong>Kısa Kodu Kullanın:</strong> Çeviri eklentisinin iFrame modülü: <code>[translate_article_form]</code></p>
            <p>Bu kısa kodu sayfalara veya yazılara ekleyerek çeviri formunu görüntüleyebilirsiniz.</p>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('ai-translator-plugin-settings-group');
            do_settings_sections('ai-translator-plugin-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="ai_google_api_key">Google Cloud API Key</label>
                    </th>
                    <td>
                        <input type="text" name="ai_google_api_key" id="ai_google_api_key" value="<?php echo esc_attr(get_option('ai_google_api_key')); ?>" class="regular-text" />
                        <p class="description">Google Cloud Translation API kullanmak için API key'inizi buraya girin.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="ai_deepl_api_key">DeepL API Key</label>
                    </th>
                    <td>
                        <input type="text" name="ai_deepl_api_key" id="ai_deepl_api_key" value="<?php echo esc_attr(get_option('ai_deepl_api_key')); ?>" class="regular-text" />
                        <p class="description">DeepL API kullanmak için API key'inizi buraya girin.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="ai_preferred_api">Tercih Edilen Çeviri API</label>
                    </th>
                    <td>
                        <select name="ai_preferred_api" id="ai_preferred_api">
                            <option value="deepl" <?php selected(get_option('ai_preferred_api'), 'deepl'); ?>>DeepL</option>
                            <option value="google" <?php selected(get_option('ai_preferred_api'), 'google'); ?>>Google Translate</option>
                        </select>
                        <p class="description">Çeviri işlemleri için hangi API'nin kullanılacağını seçin.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Ayarları Kaydet'); ?>
        </form>
    </div>
    <?php
}


function ai_translator_plugin_settings() {
    register_setting('ai-translator-plugin-settings-group', 'ai_google_api_key');
    register_setting('ai-translator-plugin-settings-group', 'ai_deepl_api_key');
    register_setting('ai-translator-plugin-settings-group', 'ai_preferred_api');
}

function ai_translator_plugin_admin_styles($hook) {
    // Sadece kendi ayar sayfamızdaysa yükle
    if ($hook != 'settings_page_ai-translator-plugin') {
        return;
    }

    wp_enqueue_style('ai-translator-plugin-admin-style', plugins_url('../style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'ai_translator_plugin_admin_styles');

?>
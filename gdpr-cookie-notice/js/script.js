jQuery(document).ready(function($) {
    // Обработчик кнопки "Принять"
    $('#gdpr-accept').on('click', function() {
        saveChoice('accept', true, true);
    });

    // Обработчик кнопки "Настройки"
    $('#gdpr-settings').on('click', function() {
        $('#gdpr-settings-modal').fadeIn();
    });

    // Закрытие модального окна
    $('#gdpr-modal-close').on('click', function() {
        $('#gdpr-settings-modal').fadeOut();
    });

    // Сохранение настроек
    $('#gdpr-save-settings').on('click', function() {
        const analytics = $('#gdpr-analytics-cookies').is(':checked');
        const marketing = $('#gdpr-marketing-cookies').is(':checked');
        saveChoice('custom', analytics, marketing);
    });

    function saveChoice(choice, analytics, marketing) {
        $.ajax({
            url: gdpr_params.ajax_url,
            type: 'POST',
            data: {
                action: 'gdpr_save_choice',
                choice: choice,
                analytics: analytics,
                marketing: marketing,
                nonce: gdpr_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#gdpr-cookie-notice').fadeOut();
                    $('#gdpr-settings-modal').fadeOut();
                }
            }
        });
    }
});
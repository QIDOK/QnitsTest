/**
 * URL Shortener JavaScript functionality - работа с формой без перезагрузки
 */

$(document).ready(function() {
    // Обработчик отправки формы
    $('#url-form').on('submit', function(e) {
        e.preventDefault(); // Предотвращаем стандартную отправку формы

        var $form = $(this);
        var formData = $form.serialize();
        var $submitBtn = $('#shorten-btn');
        var originalText = $submitBtn.text();

        // Проверяем поле URL
        var originalUrl = $('#url-original_url').val();
        if (!originalUrl) {
            showMessage('Пожалуйста, введите URL', 'danger');
            return false;
        }

        // Отключаем кнопку и меняем текст
        $submitBtn.prop('disabled', true).text('Обработка...');

        // Отправляем AJAX запрос
        $.ajax({
            url: '/url/create', // используем новый URL API endpoint
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Восстанавливаем кнопку
                $submitBtn.prop('disabled', false).text(originalText);

                if (response.success) {
                    // Показываем успешный результат
                    showShortUrl(response.shortUrl || response.data.short_url);
                    showMessage('Короткая ссылка успешно создана!', 'success');

                    // Очищаем форму
                    $form[0].reset();
                } else {
                    var errorMsg = response.message || response.error || 'Не удалось создать короткую ссылку';
                    showMessage(errorMsg, 'danger');
                }
            },
            error: function(xhr) {
                // Восстанавливаем кнопку
                $submitBtn.prop('disabled', false).text(originalText);

                var errorMessage = 'Произошла ошибка при обработке запроса';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showMessage(errorMessage, 'danger');
            }
        });
    });
});

/**
 * Показать сообщение пользователю с использованием шаблона из layout
 */
function showMessage(message, type) {
    // Определяем конфигурацию для разных типов сообщений
    var messageConfig = {
        'success': {
            className: 'message-success',
            icon: 'fas fa-check-circle'
        },
        'danger': {
            className: 'message-danger',
            icon: 'fas fa-exclamation-triangle'
        },
        'info': {
            className: 'message-info',
            icon: 'fas fa-info-circle'
        }
    };

    var config = messageConfig[type] || messageConfig['danger'];

    // Клонируем шаблон
    var $messageTemplate = $('#message-template .alert').clone();
    var messageId = 'message-' + Date.now();

    // Настраиваем сообщение
    $messageTemplate
        .attr('id', messageId)
        .addClass(config.className)
        .find('.message-icon').addClass(config.icon).end()
        .find('.message-text').text(message);

    // Добавляем сообщение в контейнер
    $('#flash-messages').append($messageTemplate);

    var $message = $('#' + messageId);

    // Обработчик закрытия
    $message.find('.btn-close').on('click', function() {
        hideMessage($message);
    });

    // Анимация появления
    setTimeout(function() {
        $message.css({
            'transform': 'translateY(0) scale(1)',
            'opacity': '1'
        });
    }, 50);

    // Запускаем прогресс-бар
    setTimeout(function() {
        $message.find('.progress-bar').css('transform', 'translateX(0)');
    }, 200);

    // Автоматически скрываем через 5 секунд
    setTimeout(function() {
        hideMessage($message);
    }, 5000);
}

/**
 * Скрыть сообщение с анимацией
 */
function hideMessage($message) {
    $message.css({
        'transform': 'translateY(-30px) scale(0.95)',
        'opacity': '0'
    });

    setTimeout(function() {
        $message.slideUp(400, function() {
            $(this).remove();
        });
    }, 300);
}

/**
 * Показать короткую ссылку
 */
function showShortUrl(shortUrl) {
    var html = '<div class="alert-success mt-4">' +
               '<h6><i class="fas fa-check-circle"></i> Ваша короткая ссылка:</h6>' +
               '<div class="input-group">' +
               '<input type="text" class="form-control" value="' + shortUrl + '" readonly onclick="this.select()" id="short-url-input">' +
               '<div class="input-group-append">' +
               '<button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()" title="Копировать ссылку">' +
               '<i class="fas fa-copy"></i> Копировать' +
               '</button>' +
               '</div></div>' +
               '<div class="mt-2 text-muted small">' +
               '<i class="fas fa-info-circle"></i> Нажмите на поле или кнопку для копирования ссылки' +
               '</div></div>';

    $('#ajax-result').html(html).show();
}

/**
 * Копировать ссылку в буфер обмена
 */
function copyToClipboard() {
    var copyText = document.getElementById('short-url-input');
    if (copyText) {
        copyText.select();
        copyText.setSelectionRange(0, 99999);

        try {
            // Пробуем современный Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(copyText.value).then(function() {
                    showCopySuccess();
                }).catch(function() {
                    fallbackCopy(copyText);
                });
            } else {
                fallbackCopy(copyText);
            }
        } catch (err) {
            console.error('Ошибка копирования:', err);
            showMessage('Не удалось скопировать ссылку', 'danger');
        }
    }
}

/**
 * Резервный метод копирования
 */
function fallbackCopy(copyText) {
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showMessage('Не удалось скопировать ссылку', 'danger');
    }
}

/**
 * Показать сообщение об успешном копировании
 */
function showCopySuccess() {
    showMessage('Ссылка скопирована в буфер обмена!', 'info');
}

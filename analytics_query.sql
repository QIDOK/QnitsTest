-- SQL запрос для получения аналитики по ТЗ
-- Запрос возвращает данные в формате:
-- Месяц (перехода по ссылке) | Ссылка | Кол-во переходов по ссылке | Позиция в топе месяца по переходам

SELECT
    DATE_FORMAT(uc.clicked_at, '%Y-%m') as 'Месяц (перехода по ссылке)',
    u.original_url as 'Ссылка',
    COUNT(*) as 'Кол-во переходов',
    ROW_NUMBER() OVER (
        PARTITION BY DATE_FORMAT(uc.clicked_at, '%Y-%m')
        ORDER BY COUNT(*) DESC
    ) as 'Позиция в топе месяца по переходам'
FROM url_clicks uc
INNER JOIN urls u ON uc.url_id = u.id
GROUP BY
    DATE_FORMAT(uc.clicked_at, '%Y-%m'),
    u.original_url
ORDER BY
    DATE_FORMAT(uc.clicked_at, '%Y-%m') DESC,
    COUNT(*) DESC;

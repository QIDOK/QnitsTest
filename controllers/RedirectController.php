<?php

namespace app\controllers;

use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Url;
use app\models\UrlClick;
use yii\web\Response;

/**
 * Контроллер для обработки переходов по коротким ссылкам
 */
class RedirectController extends Controller
{
    /**
     * Проверяет, является ли пользователь ботом
     *
     * @param string $userAgent
     * @return bool
     */
    private function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'slurp', 'google', 'bing', 'yahoo',
            'facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'yandex',
            'curl', 'wget', 'python', 'java', 'php', 'node', 'http'
        ];

        $userAgentLower = strtolower($userAgent);

        foreach ($botPatterns as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

	/**
	 * Перенаправление на оригинальный URL
	 *
	 * @param string $shortCode
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws Exception
	 */
    public function actionIndex(string $shortCode): Response
    {
        $url = Url::findByShortCode($shortCode);

        if (!$url) {
            throw new NotFoundHttpException('Короткая ссылка не найдена');
        }

        // Получаем данные о пользователе
        $userAgent = Yii::$app->request->userAgent;
        $ipAddress = Yii::$app->request->userIP;

        // МОМЕНТАЛЬНОЕ перенаправление - выполняется первым делом
        $response = $this->redirect($url->original_url);

        // Асинхронная обработка логирования после редиректа
        if (function_exists('fastcgi_finish_request')) {
            // Для PHP-FPM: завершаем запрос для пользователя
            fastcgi_finish_request();
        }

        // Проверяем, является ли пользователь ботом (уже после редиректа)
        $isBot = $this->isBot($userAgent);

        // Логируем клик только если это не бот
        if (!$isBot) {
            UrlClick::logClick($url, $userAgent, $ipAddress);
        }

        return $response;
    }
}

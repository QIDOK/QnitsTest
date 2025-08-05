<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\helpers\Json;
use app\models\Url;

/**
 * URL API Controller - управление короткими ссылками
 */
class UrlController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // Убираем аутентификацию для публичного API
        unset($behaviors['authenticator']);

        // Устанавливаем JSON формат ответов
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs(): array
    {
        return [
            'create' => ['POST']
        ];
    }

    /**
     * Создание короткой ссылки
     * POST /url/create
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreate(): array
    {
        $request = Yii::$app->request;
        
        // Получаем данные из POST (от ActiveForm) или JSON
        $originalUrl = null;
        
        // Сначала пробуем получить из ActiveForm POST данных
        if ($request->isPost) {
            $originalUrl = $request->post('Url')['original_url'] ?? $request->post('original_url');
        }
        
        // Если не нашли, пробуем JSON
        if (!$originalUrl) {
            $data = Json::decode($request->getRawBody(), true);
            $originalUrl = $data['original_url'] ?? $data['url'] ?? null;
        }

        if (!$originalUrl) {
            throw new BadRequestHttpException('Параметр URL обязателен для заполнения');
        }

        // Проверяем формат URL
        if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            throw new BadRequestHttpException('Неверный формат URL');
        }

        // Проверяем схему URL (только http/https)
        $parsedUrl = parse_url($originalUrl);
        if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
            throw new BadRequestHttpException('Поддерживаются только HTTP и HTTPS ссылки');
        }

        $urlModel = Url::createShortUrl($originalUrl);

        if (!$urlModel) {
            return [
                'success' => false,
                'error' => 'Не удалось создать короткую ссылку'
            ];
        }

        $shortUrl = $request->hostInfo . '/' . $urlModel->short_code;

        return [
            'success' => true,
            'message' => 'Короткая ссылка успешно создана',
            'data' => [
                'id' => $urlModel->id,
                'original_url' => $urlModel->original_url,
                'short_url' => $shortUrl,
                'short_code' => $urlModel->short_code,
                'created_at' => $urlModel->created_at,
            ]
        ];
    }
}

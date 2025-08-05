<?php

namespace app\controllers;

use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Url;
use app\models\UrlClick;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'shorten' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage with URL shortener form.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $model = new Url();
        $shortUrl = null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $urlModel = Url::createShortUrl($model->original_url);
            if ($urlModel) {
                $shortUrl = Yii::$app->request->hostInfo . '/' . $urlModel->short_code;

                // Добавляем flash сообщение об успехе
                Yii::$app->session->setFlash('success', 'Короткая ссылка успешно создана!');

                // Очищаем форму после успешного создания
                $model = new Url();
            } else {
                Yii::$app->session->setFlash('error', 'Не удалось создать короткую ссылку. Попробуйте еще раз.');
            }
        }

        return $this->render('index', [
            'model' => $model,
            'shortUrl' => $shortUrl,
        ]);
    }

	/**
	 * Displays analytics page
	 *
	 * @return string
	 * @throws Exception
	 */
    public function actionAnalytics(): string
    {
        return $this->render('analytics', ['analytics' => UrlClick::getMonthlyAnalytics()]);
    }
}

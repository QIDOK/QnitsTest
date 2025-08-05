<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "url_clicks".
 *
 * @property int $id
 * @property int $url_id
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property string $clicked_at
 *
 * @property Url $url
 */
class UrlClick extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'url_clicks';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'clicked_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['url_id'], 'required'],
            [['user_agent'], 'string'],
            [['ip_address'], 'string', 'max' => 45],
            [['clicked_at'], 'safe'],
            [['url_id'], 'exist', 'skipOnError' => true, 'targetClass' => Url::class, 'targetAttribute' => ['url_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'url_id' => 'URL ID',
            'user_agent' => 'User Agent',
            'ip_address' => 'IP Address',
            'clicked_at' => 'Clicked At',
        ];
    }

    /**
     * Gets query for the associated url.
     *
     * @return ActiveQuery
     */
    public function getUrl(): ActiveQuery
    {
        return $this->hasOne(Url::class, ['id' => 'url_id']);
    }

	/**
	 * Logs a click for the given URL
	 *
	 * @param Url $url
	 * @param string|null $userAgent
	 * @param string|null $ipAddress
	 * @return bool
	 * @throws Exception
	 */
    public static function logClick(Url $url, ?string $userAgent = null, ?string $ipAddress = null): bool
    {
        $click = new self();
        $click->url_id = $url->id;
        $click->user_agent = $userAgent;
        $click->ip_address = $ipAddress;
        
        return $click->save();
    }

	/**
	 * Получить аналитику по кликам по месяцам и URL
	 *
	 * @return array
	 * @throws Exception
	 */
    public static function getMonthlyAnalytics(): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(uc.clicked_at, '%Y-%m') as month,
                u.original_url as url,
                COUNT(*) as clicks_count,
                ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(uc.clicked_at, '%Y-%m') ORDER BY COUNT(*) DESC) as position
            FROM url_clicks uc
            INNER JOIN urls u ON uc.url_id = u.id
            GROUP BY DATE_FORMAT(uc.clicked_at, '%Y-%m'), u.original_url
            ORDER BY month DESC, clicks_count DESC
        ";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
}

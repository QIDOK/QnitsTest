<?php

namespace app\models;

use Random\RandomException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "urls".
 *
 * @property int $id
 * @property string $original_url
 * @property string $short_code
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UrlClick[] $urlClicks
 */
class Url extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'urls';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
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
            [['original_url'], 'required'],
            [['original_url'], 'url'],
            [['original_url'], 'string'],
            [['short_code'], 'string', 'max' => 10],
            [['short_code'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'original_url' => 'Original URL',
            'short_code' => 'Short Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for the associated url clicks.
     *
     * @return ActiveQuery
     */
    public function getUrlClicks(): ActiveQuery
    {
        return $this->hasMany(UrlClick::class, ['url_id' => 'id']);
    }

	/**
	 * Generates a unique short code for URL
	 *
	 * @return string
	 * @throws RandomException
	 */
    public function generateShortCode(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $shortCode = '';
            for ($i = 0; $i < 5; $i++) {
                $shortCode .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (self::find()->where(['short_code' => $shortCode])->exists());
        
        return $shortCode;
    }

	/**
	 * Creates or finds existing short URL
	 *
	 * @param string $originalUrl
	 * @return array|ActiveRecord|null
	 * @throws Exception
	 * @throws RandomException
	 */
    public static function createShortUrl(string $originalUrl): array|ActiveRecord|null {
        // Проверяем, есть ли уже такой URL
        $existingUrl = self::find()->where(['original_url' => $originalUrl])->one();
        if ($existingUrl) {
            return $existingUrl;
        }

        // Создаем новый короткий URL
        $model = new self();
        $model->original_url = $originalUrl;
        $model->short_code = $model->generateShortCode();
        
        if ($model->save()) {
            return $model;
        }
        
        return null;
    }

    /**
     * Finds URL by short code
     *
     * @param string $shortCode
     * @return ActiveRecord|array|null
     */
    public static function findByShortCode(string $shortCode): ActiveRecord|array|null
    {
        return self::find()->where(['short_code' => $shortCode])->one();
    }
}

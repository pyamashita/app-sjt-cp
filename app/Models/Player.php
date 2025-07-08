<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'name',
        'prefecture',
        'affiliation',
        'gender',
    ];

    /**
     * 都道府県のリスト
     */
    public static function getPrefectures(): array
    {
        return [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
            '岐阜県', '静岡県', '愛知県', '三重県',
            '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
            '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県',
            '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        ];
    }

    /**
     * 性別のリスト
     */
    public static function getGenders(): array
    {
        return [
            'male' => '男性',
            'female' => '女性',
            'other' => 'その他'
        ];
    }

    /**
     * 性別の日本語表示を取得
     */
    public function getGenderLabelAttribute(): string
    {
        $genders = self::getGenders();
        return $genders[$this->gender] ?? $this->gender;
    }

    /**
     * 大会選手割り当てとのリレーション
     */
    public function competitionPlayers(): HasMany
    {
        return $this->hasMany(CompetitionPlayer::class);
    }

    /**
     * 参加大会とのリレーション（多対多）
     */
    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_players')
            ->withPivot('player_number')
            ->withTimestamps();
    }

    /**
     * フルネーム（都道府県付き）を取得
     */
    public function getFullNameAttribute(): string
    {
        return $this->name . ' (' . $this->prefecture . ')';
    }

    /**
     * 検索スコープ
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('prefecture', 'like', "%{$search}%")
              ->orWhere('affiliation', 'like', "%{$search}%");
        });
    }

    /**
     * 都道府県でフィルタリング
     */
    public function scopeByPrefecture($query, $prefecture)
    {
        return $query->where('prefecture', $prefecture);
    }

    /**
     * 性別でフィルタリング
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
}
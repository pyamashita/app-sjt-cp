<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'api_token_id',
        'ip_address',
        'user_agent',
        'action',
        'access_method',
        'reason',
        'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    /**
     * リソースとのリレーション
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * APIトークンとのリレーション
     */
    public function apiToken(): BelongsTo
    {
        return $this->belongsTo(ApiToken::class);
    }

    /**
     * アクションの選択肢
     */
    public static function getActions(): array
    {
        return [
            'view' => '閲覧',
            'download' => 'ダウンロード',
            'denied' => 'アクセス拒否',
        ];
    }

    /**
     * アクセス方法の選択肢
     */
    public static function getAccessMethods(): array
    {
        return [
            'public' => '公開',
            'token' => 'トークン',
            'ip_whitelist' => 'IP許可リスト',
            'time_limited' => '時間制限',
        ];
    }

    /**
     * アクションのラベル
     */
    public function getActionLabelAttribute(): string
    {
        return self::getActions()[$this->action] ?? $this->action;
    }

    /**
     * アクセス方法のラベル
     */
    public function getAccessMethodLabelAttribute(): string
    {
        return self::getAccessMethods()[$this->access_method] ?? $this->access_method;
    }

    /**
     * 成功したアクセスのみ
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('action', ['view', 'download']);
    }

    /**
     * 拒否されたアクセスのみ
     */
    public function scopeDenied($query)
    {
        return $query->where('action', 'denied');
    }

    /**
     * 特定のリソースのログ
     */
    public function scopeForResource($query, $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    /**
     * 特定のIPアドレスのログ
     */
    public function scopeFromIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 特定の期間のログ
     */
    public function scopePeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('accessed_at', [$startDate, $endDate]);
    }

    /**
     * 今日のログ
     */
    public function scopeToday($query)
    {
        return $query->whereDate('accessed_at', today());
    }

    /**
     * 今週のログ
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('accessed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * 今月のログ
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('accessed_at', now()->month)
            ->whereYear('accessed_at', now()->year);
    }

    /**
     * ログを記録するヘルパーメソッド
     */
    public static function logAccess(
        int $resourceId,
        string $ip,
        string $action,
        ?int $apiTokenId = null,
        ?string $accessMethod = null,
        ?string $userAgent = null,
        ?string $reason = null
    ): self {
        return self::create([
            'resource_id' => $resourceId,
            'api_token_id' => $apiTokenId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action' => $action,
            'access_method' => $accessMethod,
            'reason' => $reason,
            'accessed_at' => now(),
        ]);
    }
}
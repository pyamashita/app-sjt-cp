# Laravelキューワーカー設定ガイド

## 概要

このドキュメントでは、SJT-CPアプリケーションでLaravelキューワーカーを設定する方法を説明します。キューワーカーは予約送信機能やバックグラウンド処理に必要です。

## 開発環境（Laravel Sail使用）

### 1. キューワーカーの起動

#### 手動起動（開発時のテスト用）
```bash
# フォアグラウンドで実行（デバッグ時）
./vendor/bin/sail artisan queue:work --tries=3 --timeout=90

# バックグラウンドで実行
./vendor/bin/sail artisan queue:work --tries=3 --timeout=90 --daemon &
```

#### Docker Composeでの自動起動
`docker-compose.yml`にキューワーカーサービスを追加：

```yaml
services:
  # 既存のサービス...
  
  queue:
    build:
      context: ./vendor/laravel/sail/runtimes/8.3
      dockerfile: Dockerfile
      args:
        WWWGROUP: '${WWWGROUP}'
    image: sail-8.3/app
    extra_hosts:
      - 'host.docker.internal:host-gateway'
    environment:
      WWWUSER: '${WWWUSER}'
      LARAVEL_SAIL: 1
      XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
      XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
      IGNITION_LOCAL_SITES_PATH: '${PWD}'
    volumes:
      - '.:/var/www/html'
    networks:
      - sail
    depends_on:
      - mysql
      - redis
    command: php artisan queue:work --tries=3 --timeout=90 --daemon
```

### 2. キューワーカーの管理コマンド

```bash
# ワーカーの再起動（コード変更後）
./vendor/bin/sail artisan queue:restart

# キューの状況確認
./vendor/bin/sail artisan queue:monitor

# 失敗したジョブの表示
./vendor/bin/sail artisan queue:failed

# 失敗したジョブの再実行
./vendor/bin/sail artisan queue:retry all

# キューのクリア
./vendor/bin/sail artisan queue:clear
```

## 本番環境（直接インストール）

### 1. Supervisorを使用したキューワーカー管理

#### Supervisorのインストール
```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

#### Supervisor設定ファイルの作成
`/etc/supervisor/conf.d/sjt-cp-worker.conf`を作成：

```ini
[program:sjt-cp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/sjt-cp/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --daemon
directory=/path/to/sjt-cp
autostart=true
autorestart=true
startsecs=1
startretries=3
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/sjt-cp-worker.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=2
stopwaitsecs=3600
```

#### Supervisorサービスの管理
```bash
# 設定の再読み込み
sudo supervisorctl reread
sudo supervisorctl update

# ワーカーの開始
sudo supervisorctl start sjt-cp-worker:*

# ワーカーの停止
sudo supervisorctl stop sjt-cp-worker:*

# ワーカーの再起動
sudo supervisorctl restart sjt-cp-worker:*

# ステータス確認
sudo supervisorctl status sjt-cp-worker:*
```

### 2. systemdを使用したキューワーカー管理

#### systemdサービスファイルの作成
`/etc/systemd/system/sjt-cp-queue.service`を作成：

```ini
[Unit]
Description=SJT-CP Queue Worker
After=network.target mysql.service redis.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5s
ExecStart=/usr/bin/php /path/to/sjt-cp/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --daemon
WorkingDirectory=/path/to/sjt-cp
Environment=LARAVEL_ENV=production

# ログ設定
StandardOutput=journal
StandardError=journal
SyslogIdentifier=sjt-cp-queue

[Install]
WantedBy=multi-user.target
```

#### systemdサービスの管理
```bash
# サービスの有効化
sudo systemctl enable sjt-cp-queue

# サービスの開始
sudo systemctl start sjt-cp-queue

# サービスの停止
sudo systemctl stop sjt-cp-queue

# サービスの再起動
sudo systemctl restart sjt-cp-queue

# ステータス確認
sudo systemctl status sjt-cp-queue

# ログの確認
sudo journalctl -u sjt-cp-queue -f
```

### 3. cronを使用したキューワーカー監視

キューワーカーが停止した場合の自動復旧用：

```bash
# crontabに追加
crontab -e

# 毎分チェックして、停止していたら再起動
* * * * * cd /path/to/sjt-cp && php artisan queue:work --stop-when-empty --daemon > /dev/null 2>&1
```

## 設定のポイント

### 1. 環境変数の設定
`.env`ファイルでキューの設定を確認：

```env
# キューのドライバー（database, redis, sync）
QUEUE_CONNECTION=database

# タイムゾーン設定（重要：キューワーカーの時刻表示に影響）
APP_TIMEZONE=Asia/Tokyo

# Redis使用時
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. キューテーブルの作成
データベースドライバー使用時：

```bash
# 開発環境
./vendor/bin/sail artisan queue:table
./vendor/bin/sail artisan migrate

# 本番環境
php artisan queue:table
php artisan migrate
```

### 3. 失敗したジョブテーブルの作成
```bash
# 開発環境
./vendor/bin/sail artisan queue:failed-table
./vendor/bin/sail artisan migrate

# 本番環境
php artisan queue:failed-table
php artisan migrate
```

## トラブルシューティング

### 1. キューワーカーが動作しない
```bash
# プロセス確認
ps aux | grep "queue:work"

# ログ確認
tail -f storage/logs/laravel.log

# 設定確認
php artisan config:cache
php artisan queue:restart
```

### 4. タイムゾーンが正しく表示されない
キューワーカーの時刻がUTCで表示される場合：
```bash
# .envファイルでタイムゾーンを設定
echo "APP_TIMEZONE=Asia/Tokyo" >> .env

# config/app.phpでタイムゾーン設定を環境変数から読み込むよう変更
# 'timezone' => env('APP_TIMEZONE', 'UTC'),

# 設定を再読み込み
php artisan config:cache
php artisan queue:restart
```

### 2. ジョブが失敗する
```bash
# 失敗したジョブの確認
php artisan queue:failed

# 特定のジョブの再実行
php artisan queue:retry [job-id]

# 全ての失敗したジョブの再実行
php artisan queue:retry all
```

### 3. メモリリークの対策
```bash
# メモリ制限付きで実行
php artisan queue:work --memory=512

# 一定時間で再起動
php artisan queue:work --max-time=3600
```

## 監視とメンテナンス

### 1. ログ監視
- キューワーカーのログを定期的に確認
- 失敗したジョブの原因を調査
- メモリ使用量の監視

### 2. 定期メンテナンス
```bash
# 古い失敗ジョブのクリーンアップ（週1回）
php artisan queue:prune-failed --hours=168

# ログローテーション設定
# /etc/logrotate.d/sjt-cp
/path/to/sjt-cp/storage/logs/*.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 0644 www-data www-data
}
```

## セキュリティ考慮事項

1. **実行ユーザー**: webサーバーと同じユーザー（www-data等）で実行
2. **ファイル権限**: ログファイルやストレージディレクトリの適切な権限設定
3. **環境変数**: 本番環境では適切な`.env`ファイルの保護

## 注意事項

- コードを変更した場合は必ず`queue:restart`を実行
- 本番環境では複数のワーカープロセスを起動することを推奨
- 重要なジョブの場合は`tries`を適切に設定
- データベースドライバーよりもRedisの使用を推奨（パフォーマンス向上）
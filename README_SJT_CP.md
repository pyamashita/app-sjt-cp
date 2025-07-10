# SJT-CP (SkillJapan Tools Control Panel)

## 概要
技能競技会のウェブデザイン職種の競技運営サポート用コントロールパネルです。

## ローカル開発環境のセットアップ

### サブドメインの設定

このアプリケーションは以下のサブドメインを使用します：

- **API**: `api.localhost` - REST API
- **ガイドページ**: `guide.localhost` - 競技参加者向けガイドページ

ローカル開発環境では以下の設定が必要です。

#### macOS/Linuxの場合

1. `/etc/hosts`ファイルを編集します:
```bash
sudo nano /etc/hosts
```

2. 以下の行を追加します:
```
127.0.0.1   api.localhost
127.0.0.1   guide.localhost
```

3. ファイルを保存して閉じます（Ctrl+X → Y → Enter）

#### Windowsの場合

1. 管理者権限でメモ帳を開きます
2. `C:\Windows\System32\drivers\etc\hosts`ファイルを開きます
3. 以下の行を追加します:
```
127.0.0.1   api.localhost
127.0.0.1   guide.localhost
```
4. ファイルを保存します

### 動作確認

設定後、以下のURLでアクセスできるようになります：
- **メインアプリケーション**: `http://localhost`
- **API**: `http://api.localhost`
- **ガイドページ**: `http://guide.localhost`

## 主な機能

### ガイドページ管理

競技参加者向けのガイドページを作成・管理できます：

1. **管理画面**: `http://localhost/admin/guide-pages`
2. **階層構造**: セクション → グループ → アイテム
3. **コンテンツタイプ**: リソースファイル、外部リンク
4. **プレビュー機能**: 実際の表示を確認
5. **公開設定**: 大会ごとに1つのページを有効化

### 注意事項

- Laravel Sailを使用している場合は、Dockerコンテナ内でもhostsの設定が必要な場合があります
- 本番環境では、実際のDNSレコードでサブドメインを設定してください
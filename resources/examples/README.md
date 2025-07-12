# SJT-CP WebSocket端末クライアント

このディレクトリには、SJT-CPからのメッセージを受信するための端末側実装例が含まれています。

## ファイル構成

- `websocket-server.cjs` - Node.js WebSocketサーバー実装例
- `websocket-client.html` - ブラウザー用WebSocketクライアント実装例
- `package.json` - Node.js依存関係設定
- `README.md` - このファイル

## 使用方法

### 1. Node.js WebSocketサーバー（推奨）

端末でWebSocketサーバーを起動してSJT-CPからのメッセージを受信します。

```bash
# 必要なパッケージをインストール
cd resources/examples
npm install

# WebSocketサーバーを起動
npm start
# または
node websocket-server.cjs
```

**サーバー起動後:**
- WebSocket: `ws://[端末IP]:8080/message`
- HTTP API: `http://[端末IP]:8080/api/message`
- ステータス確認: `http://[端末IP]:8080/status`

### 2. ブラウザークライアント（デモ用）

WebSocketの動作をブラウザーで確認したい場合:

```bash
# HTMLファイルをブラウザーで開く
open websocket-client.html
```

## メッセージ形式

SJT-CPから送信されるメッセージの形式:

```json
{
  "type": "message",
  "timestamp": "2025-07-12T07:31:18.000Z",
  "data": {
    "title": "メッセージタイトル",
    "content": "メッセージ本文",
    "link": "https://example.com",
    "image_url": "http://sjt-cp-server/storage/image.jpg",
    "sender": "SJT-CP"
  }
}
```

## 接続テスト

SJT-CP管理画面から端末への接続テストが可能です:

1. メッセージ作成画面を開く
2. 送信対象の端末にマウスをホバー
3. 「テスト」ボタンをクリック

## カスタマイズ

`websocket-server.cjs`の`handleMessage`関数を編集することで、受信したメッセージの処理をカスタマイズできます:

```javascript
function handleMessage(messageData) {
    // 1. 画面に通知表示
    displayNotification(messageData.data.title, messageData.data.content);
    
    // 2. ログファイルに保存
    saveMessageLog(messageData);
    
    // 3. カスタム処理
    // 例: 特定のキーワードに反応、外部APIに転送など
}
```

## 設定

環境変数でポートやホストを変更可能:

```bash
# ポート変更
PORT=9000 node websocket-server.cjs

# ホスト変更
HOST=192.168.1.100 node websocket-server.cjs
```

## トラブルシューティング

### ポートが使用中の場合
```bash
# 別のポートで起動
PORT=8081 node websocket-server.cjs
```

### ファイアウォールで接続できない場合
- ポート8080（または指定ポート）を開放
- 端末のIPアドレスがSJT-CPから到達可能か確認

### メッセージが受信できない場合
1. サーバーが起動しているか確認
2. SJT-CP側で正しいIPアドレスが設定されているか確認
3. ネットワーク接続を確認
#!/usr/bin/env node

/**
 * 端末用WebSocketサーバー (Node.js実装例)
 *
 * このサーバーはSJT-CPからのメッセージを受信するための
 * 端末側WebSocketサーバーの実装例です。
 *
 * 使用方法:
 * 1. Node.jsをインストール
 * 2. このディレクトリでパッケージをインストール: npm install ws
 * 3. このファイルを実行: node websocket-server.cjs
 *
 * 注意: このファイルはCommonJS形式で記述されています
 */

const WebSocket = require('ws');
const http = require('http');
const url = require('url');

// 設定
const PORT = process.env.PORT || 8081;
const HOST = process.env.HOST || '0.0.0.0';

// HTTPサーバー（フォールバック用）
const httpServer = http.createServer((req, res) => {
    const parsedUrl = url.parse(req.url, true);

    // CORSヘッダーを設定
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    if (parsedUrl.pathname === '/api/message' && req.method === 'POST') {
        // HTTPでのメッセージ受信（フォールバック）
        let body = '';

        req.on('data', chunk => {
            body += chunk.toString();
        });

        req.on('end', () => {
            try {
                const messageData = JSON.parse(body);
                console.log('\n=== HTTP メッセージ受信 ===');
                console.log('送信者:', messageData.data?.sender || '不明');
                console.log('タイトル:', messageData.data?.title || '無題');
                console.log('本文:', messageData.data?.content || '');
                if (messageData.data?.link) {
                    console.log('リンク:', messageData.data.link);
                }
                if (messageData.data?.image_url) {
                    console.log('画像URL:', messageData.data.image_url);
                }
                console.log('受信時刻:', new Date().toLocaleString('ja-JP'));
                console.log('========================\n');

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    message: 'メッセージを受信しました'
                }));

            } catch (error) {
                console.error('HTTP メッセージ解析エラー:', error.message);
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: false,
                    error: 'メッセージ解析エラー'
                }));
            }
        });

    } else if (parsedUrl.pathname === '/status') {
        // ステータス確認
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: 'running',
            port: PORT,
            websocket: wss ? 'active' : 'inactive',
            timestamp: new Date().toISOString()
        }));

    } else {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('Not Found');
    }
});

// WebSocketサーバー
const wss = new WebSocket.Server({
    server: httpServer,
    path: '/message'
});

// WebSocket接続管理
wss.on('connection', (ws, req) => {
    const clientIp = req.socket.remoteAddress;
    console.log(`\n[${new Date().toLocaleString('ja-JP')}] WebSocket接続: ${clientIp}`);

    // 接続確認メッセージ
    ws.send(JSON.stringify({
        type: 'connection',
        message: 'WebSocket接続が確立されました',
        timestamp: new Date().toISOString()
    }));

    // メッセージ受信
    ws.on('message', (data) => {
        try {
            const messageData = JSON.parse(data.toString());

            if (messageData.type === 'ping') {
                console.log(`[${new Date().toLocaleString('ja-JP')}] Ping受信: ${clientIp}`);

                // Pong応答
                ws.send(JSON.stringify({
                    type: 'pong',
                    timestamp: new Date().toISOString()
                }));

            } else if (messageData.type === 'message') {
                console.log('\n=== WebSocket メッセージ受信 ===');
                console.log('送信者:', messageData.data?.sender || '不明');
                console.log('タイトル:', messageData.data?.title || '無題');
                console.log('本文:', messageData.data?.content || '');
                if (messageData.data?.link) {
                    console.log('リンク:', messageData.data.link);
                }
                if (messageData.data?.image_url) {
                    console.log('画像URL:', messageData.data.image_url);
                }
                console.log('送信者IP:', clientIp);
                console.log('受信時刻:', new Date().toLocaleString('ja-JP'));
                console.log('===============================\n');

                // 受信確認応答
                ws.send(JSON.stringify({
                    type: 'received',
                    message: 'メッセージを受信しました',
                    timestamp: new Date().toISOString()
                }));

                // メッセージの内容に応じた処理をここに追加
                // 例: 画面に表示、音声通知、ログ保存など
                handleMessage(messageData);

            } else {
                console.log('不明なメッセージタイプ:', messageData.type);
            }

        } catch (error) {
            console.error('WebSocket メッセージ解析エラー:', error.message);

            ws.send(JSON.stringify({
                type: 'error',
                message: 'メッセージ解析エラー',
                timestamp: new Date().toISOString()
            }));
        }
    });

    // 接続エラー
    ws.on('error', (error) => {
        console.error(`WebSocketエラー (${clientIp}):`, error.message);
    });

    // 接続切断
    ws.on('close', (code) => {
        console.log(`\n[${new Date().toLocaleString('ja-JP')}] WebSocket切断: ${clientIp} (コード: ${code})`);
    });
});

// メッセージ処理関数
function handleMessage(messageData) {
    // ここで実際のメッセージ処理を行います
    // 例:

    // 1. 画面に通知表示
    if (messageData.data?.title || messageData.data?.content) {
        displayNotification(messageData.data.title, messageData.data.content);
    }

    // 2. ログファイルに保存
    saveMessageLog(messageData);

    // 3. 特定のキーワードに反応
    if (messageData.data?.content?.includes('緊急')) {
        handleEmergencyMessage(messageData);
    }
}

// 通知表示（実装例）
function displayNotification(title, content) {
    // 実際の端末では適切な通知システムを使用
    console.log(`📢 通知: ${title} - ${content}`);
}

// ログ保存（実装例）
function saveMessageLog(messageData) {
    const fs = require('fs');
    const logEntry = {
        timestamp: new Date().toISOString(),
        message: messageData
    };

    try {
        // ログファイルに追記
        fs.appendFileSync('message.log', JSON.stringify(logEntry) + '\n');
    } catch (error) {
        console.error('ログ保存エラー:', error.message);
    }
}

// 緊急メッセージ処理（実装例）
function handleEmergencyMessage(messageData) {
    console.log('🚨 緊急メッセージを受信しました！');
    // 特別な処理（音声アラート、画面点滅など）
}

// サーバー開始
httpServer.listen(PORT, HOST, () => {
    console.log('=================================');
    console.log('   端末用WebSocketサーバー起動');
    console.log('=================================');
    console.log(`🌐 HTTP/WebSocketサーバー: http://${HOST}:${PORT}`);
    console.log(`📡 WebSocketエンドポイント: ws://${HOST}:${PORT}/message`);
    console.log(`🔗 HTTPエンドポイント: http://${HOST}:${PORT}/api/message`);
    console.log(`📊 ステータス確認: http://${HOST}:${PORT}/status`);
    console.log('=================================');
    console.log('SJT-CPからのメッセージ受信待機中...\n');
});

// グレースフルシャットダウン
process.on('SIGINT', () => {
    console.log('\n\nサーバーを停止しています...');

    wss.clients.forEach((ws) => {
        ws.close();
    });

    httpServer.close(() => {
        console.log('サーバーが停止されました。');
        process.exit(0);
    });
});

// エラーハンドリング
process.on('uncaughtException', (error) => {
    console.error('予期しないエラー:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('未処理のPromise拒否:', reason);
});

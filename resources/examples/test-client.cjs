#!/usr/bin/env node

/**
 * WebSocketクライアントテストプログラム
 * 
 * 端末のWebSocket接続をシミュレートするためのテストクライアント
 * 
 * 使用方法:
 * node test-client.cjs [IPアドレス] [ポート]
 * 
 * 例: node test-client.cjs 192.168.2.120 8081
 */

const WebSocket = require('ws');

// コマンドライン引数の解析
const args = process.argv.slice(2);
const serverHost = args[0] || 'localhost';
const serverPort = args[1] || '8081';
const clientId = args[2] || 'test-client';

const url = `ws://${serverHost}:${serverPort}/message`;

console.log('====================================');
console.log('    WebSocketクライアントテスト');
console.log('====================================');
console.log(`接続先: ${url}`);
console.log(`クライアントID: ${clientId}`);
console.log('====================================');

const ws = new WebSocket(url);

// 接続成功
ws.on('open', function open() {
    console.log(`✅ [${new Date().toLocaleString('ja-JP')}] WebSocket接続成功`);
    
    // 定期的にPingを送信（30秒間隔）
    const pingInterval = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
            console.log(`📡 [${new Date().toLocaleString('ja-JP')}] Ping送信`);
            ws.send(JSON.stringify({
                type: 'ping',
                clientId: clientId,
                timestamp: new Date().toISOString()
            }));
        }
    }, 30000);

    // 接続終了時にインターバルをクリア
    ws.on('close', () => {
        clearInterval(pingInterval);
    });
});

// メッセージ受信
ws.on('message', function message(data) {
    try {
        const messageData = JSON.parse(data.toString());
        
        switch (messageData.type) {
            case 'connection':
                console.log(`🔗 [${new Date().toLocaleString('ja-JP')}] 接続確認: ${messageData.message}`);
                break;
            case 'pong':
                console.log(`🏓 [${new Date().toLocaleString('ja-JP')}] Pong受信`);
                break;
            case 'message':
                console.log(`\n📩 [${new Date().toLocaleString('ja-JP')}] メッセージ受信:`);
                console.log(`タイトル: ${messageData.data?.title || '無題'}`);
                console.log(`本文: ${messageData.data?.content || ''}`);
                if (messageData.data?.link) {
                    console.log(`リンク: ${messageData.data.link}`);
                }
                if (messageData.data?.image_url) {
                    console.log(`画像: ${messageData.data.image_url}`);
                }
                console.log('');
                break;
            case 'received':
                console.log(`✅ [${new Date().toLocaleString('ja-JP')}] 受信確認: ${messageData.message}`);
                break;
            default:
                console.log(`📦 [${new Date().toLocaleString('ja-JP')}] 不明なメッセージ:`, messageData);
        }
    } catch (error) {
        console.log(`📦 [${new Date().toLocaleString('ja-JP')}] 生メッセージ:`, data.toString());
    }
});

// エラー処理
ws.on('error', function error(err) {
    console.error(`❌ [${new Date().toLocaleString('ja-JP')}] WebSocketエラー:`, err.message);
});

// 接続終了
ws.on('close', function close(code, reason) {
    console.log(`🔌 [${new Date().toLocaleString('ja-JP')}] WebSocket接続終了 (コード: ${code}${reason ? `, 理由: ${reason}` : ''})`);
    process.exit(0);
});

// プロセス終了処理
process.on('SIGINT', () => {
    console.log('\n👋 接続を終了しています...');
    ws.close();
});

// キープアライブのためのメッセージ
setTimeout(() => {
    console.log('💡 このクライアントは30秒ごとにPingを送信し、接続を維持します。');
    console.log('💡 Ctrl+Cで終了できます。');
}, 1000);
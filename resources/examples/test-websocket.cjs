#!/usr/bin/env node

const WebSocket = require('ws');

const url = 'ws://127.0.0.1:8081/message';

console.log('WebSocket接続テスト開始:', url);

const ws = new WebSocket(url);

ws.on('open', function open() {
    console.log('✅ WebSocket接続成功');
    
    // Pingメッセージを送信
    const pingMessage = JSON.stringify({
        type: 'ping'
    });
    
    console.log('Pingメッセージ送信:', pingMessage);
    ws.send(pingMessage);
});

ws.on('message', function message(data) {
    console.log('📩 レスポンス受信:', data.toString());
    ws.close();
});

ws.on('error', function error(err) {
    console.error('❌ WebSocketエラー:', err.message);
});

ws.on('close', function close() {
    console.log('🔌 WebSocket接続終了');
    process.exit(0);
});

// 5秒後にタイムアウト
setTimeout(() => {
    console.log('⏰ タイムアウト');
    ws.close();
    process.exit(1);
}, 5000);
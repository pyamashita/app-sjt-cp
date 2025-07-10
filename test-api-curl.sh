#!/bin/bash

# APIのベースURL
API_BASE_URL="http://api.localhost"

echo "=== API CORS テストスクリプト ==="
echo ""

# 1. 基本的なGETリクエスト
echo "1. 基本的なGETリクエスト:"
echo "コマンド: curl -i $API_BASE_URL/resources"
curl -i "$API_BASE_URL/resources"
echo -e "\n\n"

# 2. CORSヘッダーの確認（OPTIONSリクエスト）
echo "2. CORSプリフライトリクエスト:"
echo "コマンド: curl -i -X OPTIONS -H 'Origin: http://localhost' -H 'Access-Control-Request-Method: GET' $API_BASE_URL/resources"
curl -i -X OPTIONS \
  -H "Origin: http://localhost" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: authorization" \
  "$API_BASE_URL/resources"
echo -e "\n\n"

# 3. Originヘッダー付きGETリクエスト
echo "3. Originヘッダー付きGETリクエスト:"
echo "コマンド: curl -i -H 'Origin: http://localhost' $API_BASE_URL/resources"
curl -i -H "Origin: http://localhost" "$API_BASE_URL/resources"
echo -e "\n\n"

# 4. 認証付きリクエスト（トークンが必要）
echo "4. 認証付きリクエスト（例）:"
echo "コマンド例: curl -i -H 'Authorization: Bearer YOUR_TOKEN' -H 'Origin: http://localhost' $API_BASE_URL/resources/stats"
echo "※ 実際のトークンに置き換えて実行してください"
echo -e "\n\n"

# 5. カテゴリ一覧の取得
echo "5. カテゴリ一覧の取得:"
echo "コマンド: curl -i -H 'Origin: http://localhost' $API_BASE_URL/resources/categories"
curl -i -H "Origin: http://localhost" "$API_BASE_URL/resources/categories"
echo -e "\n\n"

echo "=== テスト完了 ==="
echo ""
echo "ヒント:"
echo "- 'Access-Control-Allow-Origin' ヘッダーが含まれているか確認してください"
echo "- 'Access-Control-Allow-Credentials: true' が設定されているか確認してください"
echo "- エラーが発生した場合は、/etc/hosts に '127.0.0.1 api.localhost' が設定されているか確認してください"
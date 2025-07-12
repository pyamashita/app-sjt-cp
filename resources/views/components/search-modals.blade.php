<!-- 大会検索モーダル -->
<div id="competition-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-medium text-gray-900">大会を選択</h3>
                <button type="button" onclick="closeCompetitionModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- 検索フィールド -->
            <div class="mb-4">
                <input type="text" id="competition-search" placeholder="大会名で検索..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- 検索結果 -->
            <div id="competition-results" class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                <div class="p-4 text-center text-gray-500">
                    検索条件を入力してください
                </div>
            </div>
            
            <!-- フッター -->
            <div class="flex justify-end pt-4">
                <button type="button" onclick="closeCompetitionModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 選手検索モーダル -->
<div id="player-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-medium text-gray-900">選手を選択</h3>
                <button type="button" onclick="closePlayerModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- 検索フィールド -->
            <div class="mb-4">
                <input type="text" id="player-search" placeholder="選手名・かな名で検索..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- 検索結果 -->
            <div id="player-results" class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                <div class="p-4 text-center text-gray-500">
                    検索条件を入力してください
                </div>
            </div>
            
            <!-- フッター -->
            <div class="flex justify-end pt-4">
                <button type="button" onclick="closePlayerModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>

<!-- リソース検索モーダル -->
<div id="resource-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-medium text-gray-900">リソースを選択</h3>
                <button type="button" onclick="closeResourceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- 検索フィールド -->
            <div class="mb-4">
                <input type="text" id="resource-search" placeholder="リソース名で検索..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- 検索結果 -->
            <div id="resource-results" class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                <div class="p-4 text-center text-gray-500">
                    検索条件を入力してください
                </div>
            </div>
            
            <!-- フッター -->
            <div class="flex justify-end pt-4">
                <button type="button" onclick="closeResourceModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentResourceFieldName = null;

// 大会検索モーダル
function showCompetitionModal() {
    document.getElementById('competition-modal').classList.remove('hidden');
    document.getElementById('competition-search').focus();
    
    // 検索フィールドにイベントリスナーを追加
    const searchInput = document.getElementById('competition-search');
    searchInput.addEventListener('input', debounce(searchCompetitions, 300));
}

function closeCompetitionModal() {
    document.getElementById('competition-modal').classList.add('hidden');
    document.getElementById('competition-search').value = '';
    document.getElementById('competition-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
}

function searchCompetitions() {
    const query = document.getElementById('competition-search').value;
    if (query.length < 1) {
        document.getElementById('competition-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
        return;
    }
    
    fetch(`/admin/api/collections/competitions?search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('competition-results');
            if (data.competitions.length === 0) {
                resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">該当する大会が見つかりませんでした</div>';
                return;
            }
            
            let html = '';
            data.competitions.forEach(competition => {
                html += `
                    <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer" onclick="selectCompetition(${competition.id}, '${competition.name}')">
                        <div class="font-medium text-gray-900">${competition.name}</div>
                        <div class="text-sm text-gray-500">${competition.year}年 - ${competition.status}</div>
                    </div>
                `;
            });
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('competition-results').innerHTML = '<div class="p-4 text-center text-red-500">検索に失敗しました</div>';
        });
}

function selectCompetition(id, name) {
    document.getElementById('competition_id').value = id;
    
    // 大会選択が変更されたことを通知
    const event = new Event('change', { bubbles: true });
    document.getElementById('competition_id').dispatchEvent(event);
    
    closeCompetitionModal();
}

// 選手検索モーダル
function showPlayerModal() {
    document.getElementById('player-modal').classList.remove('hidden');
    document.getElementById('player-search').focus();
    
    // 検索フィールドにイベントリスナーを追加
    const searchInput = document.getElementById('player-search');
    searchInput.addEventListener('input', debounce(searchPlayers, 300));
}

function closePlayerModal() {
    document.getElementById('player-modal').classList.add('hidden');
    document.getElementById('player-search').value = '';
    document.getElementById('player-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
}

function searchPlayers() {
    const query = document.getElementById('player-search').value;
    if (query.length < 1) {
        document.getElementById('player-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
        return;
    }
    
    const competitionId = document.getElementById('competition_id')?.value;
    let url = `/admin/api/collections/players?search=${encodeURIComponent(query)}`;
    if (competitionId) {
        url += `&competition_id=${competitionId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('player-results');
            if (data.players.length === 0) {
                resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">該当する選手が見つかりませんでした</div>';
                return;
            }
            
            let html = '';
            data.players.forEach(player => {
                const displayName = player.player_number ? `${player.player_number} - ${player.name}` : player.name;
                html += `
                    <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer" onclick="selectPlayer(${player.id}, '${displayName}')">
                        <div class="font-medium text-gray-900">${displayName}</div>
                        <div class="text-sm text-gray-500">${player.name_kana || ''}</div>
                    </div>
                `;
            });
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('player-results').innerHTML = '<div class="p-4 text-center text-red-500">検索に失敗しました</div>';
        });
}

function selectPlayer(id, name) {
    document.getElementById('player_id').value = id;
    closePlayerModal();
}

// リソース検索モーダル
function showResourceModal(fieldName) {
    currentResourceFieldName = fieldName;
    document.getElementById('resource-modal').classList.remove('hidden');
    document.getElementById('resource-search').focus();
    
    // 検索フィールドにイベントリスナーを追加
    const searchInput = document.getElementById('resource-search');
    searchInput.addEventListener('input', debounce(searchResources, 300));
}

function closeResourceModal() {
    document.getElementById('resource-modal').classList.add('hidden');
    document.getElementById('resource-search').value = '';
    document.getElementById('resource-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
    currentResourceFieldName = null;
}

function searchResources() {
    const query = document.getElementById('resource-search').value;
    if (query.length < 1) {
        document.getElementById('resource-results').innerHTML = '<div class="p-4 text-center text-gray-500">検索条件を入力してください</div>';
        return;
    }
    
    fetch(`/admin/api/resources?search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('resource-results');
            if (data.resources.length === 0) {
                resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">該当するリソースが見つかりませんでした</div>';
                return;
            }
            
            let html = '';
            data.resources.forEach(resource => {
                html += `
                    <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer" onclick="selectResource(${resource.id}, '${resource.original_name}')">
                        <div class="font-medium text-gray-900">${resource.original_name}</div>
                        <div class="text-sm text-gray-500">${resource.file_type} - ${resource.file_size_formatted}</div>
                    </div>
                `;
            });
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('resource-results').innerHTML = '<div class="p-4 text-center text-red-500">検索に失敗しました</div>';
        });
}

function selectResource(id, name) {
    if (currentResourceFieldName) {
        document.getElementById(currentResourceFieldName).value = id;
    }
    closeResourceModal();
}

// デバウンス関数
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
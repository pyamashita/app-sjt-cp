@extends('layouts.admin')

@section('title', 'ユーザー詳細')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">ユーザー詳細</h1>
                <div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 編集
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> 戻る
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">基本情報</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">ID</div>
                                <div class="col-md-9">{{ $user->id }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">氏名</div>
                                <div class="col-md-9">{{ $user->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">メールアドレス</div>
                                <div class="col-md-9">{{ $user->email }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">役割</div>
                                <div class="col-md-9">
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">管理者</span>
                                    @elseif($user->role === '競技委員')
                                        <span class="badge bg-primary">競技委員</span>
                                    @else
                                        <span class="badge bg-secondary">補佐員</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">登録日時</div>
                                <div class="col-md-9">{{ $user->created_at->format('Y年m月d日 H:i') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 fw-bold">更新日時</div>
                                <div class="col-md-9">{{ $user->updated_at->format('Y年m月d日 H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">パスワード変更</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.change-password', $user) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label">新しいパスワード</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">パスワード（確認）</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-key"></i> パスワード変更
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($user->id !== auth()->id())
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">危険な操作</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('本当にこのユーザーを削除しますか？この操作は取り消せません。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> ユーザーを削除
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
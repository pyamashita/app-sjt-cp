@extends('layouts.admin')

@section('title', 'ユーザー管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">ユーザー管理</h1>
                <div>
                    @if($pendingRegistrations > 0)
                        <a href="{{ route('admin.users.registrations') }}" class="btn btn-warning me-2">
                            <i class="fas fa-user-clock"></i> 申請承認待ち ({{ $pendingRegistrations }})
                        </a>
                    @endif
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 新規作成
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if($users->isEmpty())
                        <p class="text-center py-5">ユーザーが登録されていません。</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>氏名</th>
                                        <th>メールアドレス</th>
                                        <th>役割</th>
                                        <th>登録日時</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->role === 'admin')
                                                    <span class="badge bg-danger">管理者</span>
                                                @elseif($user->role === '競技委員')
                                                    <span class="badge bg-primary">競技委員</span>
                                                @else
                                                    <span class="badge bg-secondary">補佐員</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($user->id !== auth()->id())
                                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'ユーザー登録申請')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">ユーザー登録申請</h1>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> ユーザー管理へ戻る
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if($registrations->isEmpty())
                        <p class="text-center py-5">登録申請はありません。</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>申請日時</th>
                                        <th>氏名</th>
                                        <th>メールアドレス</th>
                                        <th>希望役割</th>
                                        <th>申請理由</th>
                                        <th>状態</th>
                                        <th>承認者</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registrations as $registration)
                                        <tr>
                                            <td>{{ $registration->created_at->format('Y/m/d H:i') }}</td>
                                            <td>{{ $registration->name }}</td>
                                            <td>{{ $registration->email }}</td>
                                            <td>
                                                @if($registration->role === '競技委員')
                                                    <span class="badge bg-primary">競技委員</span>
                                                @else
                                                    <span class="badge bg-secondary">補佐員</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->reason)
                                                    <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#reasonModal{{ $registration->id }}">
                                                        表示
                                                    </button>
                                                    
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="reasonModal{{ $registration->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">申請理由</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    {{ $registration->reason }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->status === 'pending')
                                                    <span class="badge bg-warning">承認待ち</span>
                                                @elseif($registration->status === 'approved')
                                                    <span class="badge bg-success">承認済み</span>
                                                @else
                                                    <span class="badge bg-danger">却下</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->approver)
                                                    {{ $registration->approver->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $registration->approved_at->format('Y/m/d H:i') }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->status === 'pending')
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form action="{{ route('admin.users.registrations.approve', $registration) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success" onclick="return confirm('この申請を承認しますか？')">
                                                                <i class="fas fa-check"></i> 承認
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.users.registrations.reject', $registration) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger" onclick="return confirm('この申請を却下しますか？')">
                                                                <i class="fas fa-times"></i> 却下
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted">処理済み</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $registrations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
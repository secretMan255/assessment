<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteService{
    public function list(User $user,  array $filters = []): LengthAwarePaginator {
        $perPage = (int)($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 100));

        $search = trim((string)($filters['search'] ?? ''));
        $sort = (string)($filters['sort']   ?? 'id');
        $order   = (string)($filters['order']  ?? 'desc');
        $page    = $filters['page'] ?? null;

        $allowedSorts = ['id', 'title', 'created_at', 'updated_at'];

        $query = Note::query()
                ->where('user_id', $user->id)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
                })
                ->when(in_array($sort, $allowedSorts, true),
                    fn ($q) => $q->orderBy($sort, $order),
                    fn ($q) => $q->latest('id')
            );

        $paginator = $page
            ? $query->paginate($perPage, ['*'], 'page', (int)$page)
            : $query->paginate($perPage);

        return $paginator->withQueryString();
    }

    public function create(User $user, array $data): Note {
        $data['user_id'] = $user->id;
        return Note::create($data);
    }

    public function update(Note $note, array $data): Note {
        $note->update($data);
        return $note;
    }

    public function delete(Note $note): void {
        $note->delete();
    }
}
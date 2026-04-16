<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table      = 'comments';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'email', 'text'];

    protected $useTimestamps = false;

    public function getComments(string $sort, string $dir, int $limit, int $offset): array
    {
        $allowed_sort = ['id', 'created_at'];
        $allowed_dir  = ['asc', 'desc'];

        if (!in_array($sort, $allowed_sort)) {
            $sort = 'id';
        }
        if (!in_array($dir, $allowed_dir)) {
            $dir = 'desc';
        }

        return $this->orderBy($sort, $dir)
                    ->findAll($limit, $offset);
    }
}

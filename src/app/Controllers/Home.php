<?php

namespace App\Controllers;

use App\Models\CommentModel;

class Home extends BaseController
{
    private CommentModel $commentModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->commentModel = new CommentModel();
    }

    public function index()
    {
        $sort = $this->request->getGet('sort') ?? 'id';
        $dir  = $this->request->getGet('dir')  ?? 'desc';
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $perPage = 3;
        $total   = $this->commentModel->countAll();
        $offset  = ($page - 1) * $perPage;

        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page   = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $comments = $this->commentModel->getComments($sort, $dir, $perPage, $offset);

        return view('comments', [
            'comments'   => $comments,
            'sort'       => $sort,
            'dir'        => $dir,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'text'  => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $this->commentModel->insert([
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'text'  => $this->request->getPost('text'),
        ]);

        return $this->response->setJSON([
            'success'   => true,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        $comment = $this->commentModel->find($id);
        if (!$comment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Not found']);
        }

        $this->commentModel->delete($id);

        return $this->response->setJSON([
            'success'   => true,
            'csrf_hash' => csrf_hash(),
        ]);
    }
}

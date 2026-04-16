<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>Комментарии</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .comment-card {
            border-left: 3px solid #007bff;
        }
        .sort-link {
            text-decoration: none;
            color: inherit;
        }
        .sort-link:hover {
            text-decoration: underline;
        }
        .sort-active {
            font-weight: 600;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4">Комментарии</h2>

    <div class="mb-3 d-flex align-items-center">
        <span class="mr-2 text-muted small">Сортировка:</span>

        <?php
        $idDir   = ($sort === 'id') ? ($dir === 'asc' ? 'desc' : 'asc') : 'desc';
        $dateDir = ($sort === 'created_at') ? ($dir === 'asc' ? 'desc' : 'asc') : 'desc';
        $idArrow   = ($sort === 'id') ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $dateArrow = ($sort === 'created_at') ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        ?>

        <a href="/?sort=id&dir=<?= $idDir ?>&page=1"
           class="sort-link mr-3 <?= $sort === 'id' ? 'sort-active' : '' ?>">
            По ID<?= $idArrow ?>
        </a>
        <a href="/?sort=created_at&dir=<?= $dateDir ?>&page=1"
           class="sort-link <?= $sort === 'created_at' ? 'sort-active' : '' ?>">
            По дате<?= $dateArrow ?>
        </a>
    </div>

    <div id="comments-list">
        <?php if (empty($comments)): ?>
            <p class="text-muted">Комментариев пока нет.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="card comment-card mb-3" data-id="<?= $c['id'] ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= esc($c['name']) ?></h6>
                            <small class="text-muted"><?= esc($c['email']) ?></small>
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted mr-3"><?= $c['created_at'] ?></small>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $c['id'] ?>">
                                Удалить
                            </button>
                        </div>
                    </div>
                    <p class="card-text mt-2 mb-0"><?= esc($c['text']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="my-3">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="/?page=<?= $i ?>&sort=<?= $sort ?>&dir=<?= $dir ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-header">Добавить комментарий</div>
        <div class="card-body">
            <div id="form-alert" class="d-none"></div>
            <form id="comment-form" novalidate>
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" class="form-control" id="name" name="name"
                           placeholder="Ваше имя" required minlength="2" maxlength="100">
                    <div class="invalid-feedback" id="err-name"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="example@mail.com" required maxlength="150">
                    <div class="invalid-feedback" id="err-email"></div>
                </div>
                <div class="form-group">
                    <label for="text">Комментарий</label>
                    <textarea class="form-control" id="text" name="text" rows="3"
                              placeholder="Введите текст комментария" required minlength="3"></textarea>
                    <div class="invalid-feedback" id="err-text"></div>
                </div>
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    Отправить
                </button>
            </form>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function updateCsrf(hash) {
        if (hash) {
            $('meta[name="csrf-token"]').attr('content', hash);
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': hash } });
        }
    }

    function clearErrors() {
        $('#comment-form .form-control').removeClass('is-invalid is-valid');
        $('.invalid-feedback').text('');
        $('#form-alert').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
    }

    function showFieldError(field, msg) {
        $('#' + field).addClass('is-invalid');
        $('#err-' + field).text(msg);
    }

    function validateForm() {
        var valid = true;
        var name  = $.trim($('#name').val());
        var email = $.trim($('#email').val());
        var text  = $.trim($('#text').val());
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (name.length < 2) {
            showFieldError('name', 'Имя должно быть не менее 2 символов');
            valid = false;
        }
        if (!emailRegex.test(email)) {
            showFieldError('email', 'Введите корректный email');
            valid = false;
        }
        if (text.length < 3) {
            showFieldError('text', 'Комментарий слишком короткий');
            valid = false;
        }
        return valid;
    }

    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        if (!validateForm()) return;

        var $btn = $('#submit-btn');
        $btn.prop('disabled', true).text('Отправка...');

        $.post('/comments/store', $(this).serialize())
            .done(function (data) {
                updateCsrf(data.csrf_hash);
                if (data.success) {
                    $('#comment-form')[0].reset();
                    location.reload();
                } else {
                    $.each(data.errors, function (field, msg) {
                        showFieldError(field, msg);
                    });
                }
            })
            .fail(function () {
                $('#form-alert')
                    .removeClass('d-none')
                    .addClass('alert alert-danger')
                    .text('Произошла ошибка, попробуйте ещё раз.');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Отправить');
            });
    });

    $(document).on('click', '.btn-delete', function () {
        if (!confirm('Удалить комментарий?')) return;

        var $card = $(this).closest('.card');
        var id    = $(this).data('id');

        $.post('/comments/delete/' + id)
            .done(function (data) {
                updateCsrf(data.csrf_hash);
                if (data.success) {
                    $card.fadeOut(200, function () {
                        $(this).remove();
                        if ($('#comments-list .card').length === 0) {
                            $('#comments-list').html('<p class="text-muted">Комментариев пока нет.</p>');
                        }
                    });
                }
            })
            .fail(function () {
                alert('Не удалось удалить комментарий.');
            });
    });

});
</script>
</body>
</html>

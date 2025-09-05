<?php
require_once __DIR__ . '/../../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard');
}

$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #2563eb 0%, #14b8a6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #14b8a6 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #14b8a6 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: transform 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        .input-group {
            margin-bottom: 1.5rem;
        }
        .input-group-text {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-right: none;
            color: #6b7280;
        }
        .form-control:focus + .input-group-text,
        .input-group-text:focus-within {
            border-color: #2563eb;
        }
        .demo-credentials {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-shop display-4 mb-3"></i>
                        <h3 class="mb-0"><?= APP_NAME ?></h3>
                        <p class="mb-0 opacity-75">Sistema de Facturación</p>
                    </div>
                    
                    <div class="login-body">
                        <form id="loginForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Usuario" required>
                            </div>
                            
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                            </div>
                            
                            <button type="submit" class="btn btn-login w-100 mb-3">Iniciar Sesión</button>
                        </form>
                        
                        <div class="demo-credentials">
                            <h6 class="text-muted mb-2"><i class="bi bi-info-circle"></i> Credenciales de prueba:</h6>
                            <p class="mb-1"><strong>Admin:</strong> admin / password</p>
                            <p class="mb-0"><strong>Vendedor:</strong> vendedor1 / password</p>
                        </div>
                        
                        <div id="loginAlert" class="alert d-none mt-3" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const alertDiv = $('#loginAlert');

                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...');
                alertDiv.removeClass('d-none alert-success alert-danger');

                $.ajax({
                    url: '<?= BASE_URL ?>api/auth/login',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json'
                })
                .done(function(response) {
                    if (response.success) {
                        alertDiv.addClass('alert-success').text(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect || '<?= BASE_URL ?>dashboard';
                        }, 1000);
                    } else {
                        alertDiv.addClass('alert-danger').text(response.message);
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    const message = response.message || 'Error de conexión. Intente nuevamente.';
                    alertDiv.addClass('alert-danger').text(message);
                })
                .always(function() {
                    submitBtn.prop('disabled', false).text('Iniciar Sesión');
                });
            });

            $('input[name="username"]').focus();
        });
    </script>
</body>
</html>

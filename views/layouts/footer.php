<?php if (isLoggedIn()): ?>
                    </div>
                </main>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>js/main.js"></script>
    
    <script>
        // Global AJAX setup
        $.ajaxSetup({
            beforeSend: function(xhr) {
                // Add CSRF token to all AJAX requests
                const token = $('meta[name="csrf-token"]').attr('content');
                if (token) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    window.location.href = '/pos-system/auth/login.php';
                }
            }
        });
        
        // Logout function
        function logout() {
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                $.post('<?= BASE_URL ?>api/auth/logout')
                .done(function() {
                    window.location.href = '<?= BASE_URL ?>auth/login.php';
                })
                .fail(function() {
                    window.location.href = '<?= BASE_URL ?>auth/login.php';
                });
            }
        }
        
        // Format currency
        function formatCurrency(amount) {
            return 'Bs. ' + parseFloat(amount).toFixed(2);
        }
        
        // Show loading state
        function showLoading(element) {
            const $element = $(element);
            $element.prop('disabled', true);
            const originalText = $element.text();
            $element.data('original-text', originalText);
            $element.html('<span class="loading"></span> Cargando...');
        }
        
        function hideLoading(element) {
            const $element = $(element);
            $element.prop('disabled', false);
            $element.text($element.data('original-text'));
        }
        
        // Show toast notifications
        function showToast(message, type = 'success') {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
        
        // Session check
        setInterval(function() {
            $.get('<?= BASE_URL ?>api/auth/check')
            .fail(function() {
                window.location.href = '<?= BASE_URL ?>auth/login.php';
            });
        }, 300000); // cada 5 min
    </script>
</body>
</html>